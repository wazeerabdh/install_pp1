<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Model\CustomerAddress;
use App\Model\DMReview;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use function App\CentralLogics\translate;

class OrderController extends Controller
{
    public function __construct(
        private CustomerAddress $customerAddress,
        private DMReview        $dmReview,
        private Order           $order,
        private OrderDetail     $orderDetail,
        private Product         $product,
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function trackOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = $this->order->where(['id' => $request['order_id'], 'user_id' => $request->user()->id])->first();

        if (!isset($order)) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('Order not found')]
                ]
            ], 404);
        }

        return response()->json(OrderLogic::track_order($request['order_id']), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required',
            'delivery_address_id' => 'required',
            'order_type' => 'required|in:self_pickup,delivery',
            'branch_id' => 'required'
        ]);

        if (count($request['cart']) < 1) {
            return response()->json(['errors' => [['code' => 'empty-cart', 'message' => translate('cart is empty')]]], 403);
        }

        foreach ($request['cart'] as $c) {
            $product = $this->product->find($c['product_id']);
            if (count(json_decode($product['variations'], true)) > 0) {
                $type = $c['variation'][0]['type'];
                foreach (json_decode($product['variations'], true) as $var) {
                    if ($type == $var['type'] && $var['stock'] < $c['quantity']) {
                        $validator->getMessageBag()->add('stock', 'One or more product stock is insufficient!');
                    }
                }
            } else {
                if ($product['total_stock'] < $c['quantity']) {
                    $validator->getMessageBag()->add('stock', 'One or more product stock is insufficient!');
                }
            }
        }

        if ($validator->getMessageBag()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        try {
            $orderId = 100000 + $this->order->all()->count() + 1;

            $or = [
                'id' => $orderId,
                'user_id' => $request->user()->id,
                'order_amount' => $request['order_amount'],
                'coupon_discount_amount' => $request->coupon_discount_amount,
                'coupon_discount_title' => $request->coupon_discount_title == 0 ? null : 'coupon_discount_title',
                'payment_status' => $request->payment_method == 'cash_on_delivery' ? 'unpaid' : 'paid',
                'order_status' => $request->payment_method == 'cash_on_delivery' ? 'pending' : 'confirmed',
                'coupon_code' => $request['coupon_code'],
                'payment_method' => $request->payment_method,
                'transaction_reference' =>  $request->transaction_reference ?? null,
                'order_note' => $request['order_note'],
                'order_type' => $request['order_type'],
                'branch_id' => $request['branch_id'],
                'delivery_address_id' => $request->delivery_address_id,
                'delivery_charge' => $request['order_type'] == 'self_pickup' ? 0 : Helpers::get_delivery_charge($request['distance']),
                'delivery_address' => json_encode($this->customerAddress->find($request->delivery_address_id) ?? null),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $totalTaxAmount = 0;

            foreach ($request['cart'] as $c) {
                $product = $this->product->find($c['product_id']);
                if (count(json_decode($product['variations'], true)) > 0) {
                    $price = Helpers::variation_price($product, json_encode($c['variation']));
                } else {
                    $price = $product['price'];
                }
                $orderDetails = [
                    'order_id' => $orderId,
                    'product_id' => $c['product_id'],
                    'product_details' => $product,
                    'quantity' => $c['quantity'],
                    'price' => $price,
                    'unit' => $product['unit'],
                    'tax_amount' => Helpers::tax_calculate($product, $price),
                    'discount_on_product' => Helpers::discount_calculate($product, $price),
                    'discount_type' => 'discount_on_product',
                    'variant' => json_encode($c['variant']),
                    'variation' => json_encode($c['variation']),
                    'is_stock_decreased' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $totalTaxAmount += $orderDetails['tax_amount'] * $c['quantity'];

                if (count(json_decode($product['variations'], true)) > 0) {
                    $type = $c['variation'][0]['type'];
                    $varStore = [];
                    foreach (json_decode($product['variations'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['stock'] -= $c['quantity'];
                        }
                        $varStore[] = $var;
                    }
                    $this->product->where(['id' => $product['id']])->update([
                        'variations' => json_encode($varStore),
                        'total_stock' => $product['total_stock'] - $c['quantity']
                    ]);
                } else {
                    $this->product->where(['id' => $product['id']])->update([
                        'total_stock' => $product['total_stock'] - $c['quantity']
                    ]);
                }

                DB::table('order_details')->insert($orderDetails);
            }

            $or['total_tax_amount'] = $totalTaxAmount;
            DB::table('orders')->insertGetId($or);

            $fcmToken = $request->user()->cm_firebase_token;
            $value = Helpers::order_status_update_message('pending');
            try {
                if ($value) {
                    $data = [
                        'title' => 'Order',
                        'description' => $value,
                        'order_id' => $orderId,
                        'image' => '',
                        'type' => 'general',
                    ];
                    Helpers::send_push_notif_to_device($fcmToken, $data);
                }

                $emailServices = Helpers::get_business_settings('mail_config');
                if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                    Mail::to($request->user()->email)->send(new \App\Mail\OrderPlaced($orderId));
                }

            } catch (\Exception $e) {
            }

            return response()->json([
                'message' => translate('Order placed successfully'),
                'order_id' => $orderId
            ], 200);

        } catch (\Exception $e) {
            return response()->json([$e], 403);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderList(Request $request): JsonResponse
    {
        $orders = $this->order->with(['customer', 'delivery_man.rating'])
            ->withCount('details')
            ->where(['user_id' => $request->user()->id])
            ->get();

        $orders = $this->order->with(['customer', 'delivery_man.rating', 'details'])
            ->withCount('details')
            ->where(['user_id' => $request->user()->id])
            ->get();

        $orders->each(function ($order) {
            $order->total_quantity = $order->details->sum('quantity');
        });

        $orders->map(function ($data) {
            $data['deliveryman_review_count'] = $this->dmReview->where(['delivery_man_id' => $data['delivery_man_id'], 'order_id' => $data['id']])->count();
            return $data;
        });

        return response()->json($orders->map(function ($data) {
            $data->details_count = (integer)$data->details_count;
            return $data;
        }), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $details = $this->orderDetail->with(['order'])
            ->where(['order_id' => $request['order_id']])
            ->whereHas('order', function ($q) use ($request) {
                $q->where(['user_id' => $request->user()->id]);
            })
            ->get();

        if ($details->count() < 1) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('Order not found!')]
                ]
            ], 404);
        }

        $details = Helpers::order_details_formatter($details);
        return response()->json($details, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $order = $this->order::find($request['order_id']);

        if (!isset($order)) {
            return response()->json(['errors' => [['code' => 'order', 'message' => 'Order not found!']]], 404);
        }

        if ($order->order_status != 'pending') {
            return response()->json(['errors' => [['code' => 'order', 'message' => 'Order can only cancel when order status is pending!']]], 403);
        }

        if ($this->order->where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->first()) {

            $order = $this->order->with(['details'])->where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->first();

            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 1) {
                    $product = $this->product->find($detail['product_id']);

                    if ($product) {
                        if (count(json_decode($product['variations'], true)) > 0) {
                            $type = json_decode($detail['variation'])[0]->type;
                            $varStore = [];
                            foreach (json_decode($product['variations'], true) as $var) {
                                if ($type == $var['type']) {
                                    $var['stock'] += $detail['quantity'];
                                }
                                $varStore[] = $var;
                            }
                            $this->product->where(['id' => $product['id']])->update([
                                'variations' => json_encode($varStore),
                                'total_stock' => $product['total_stock'] + $detail['quantity'],
                            ]);
                        } else {
                            $this->product->where(['id' => $product['id']])->update([
                                'total_stock' => $product['total_stock'] + $detail['quantity'],
                            ]);
                        }
                    }

                    $this->orderDetail->where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 0
                    ]);
                }
            }

            $this->order->where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->update([
                'order_status' => 'canceled'
            ]);
            return response()->json(['message' => translate('Order canceled')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('not found')]
            ]
        ], 401);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePaymentMethod(Request $request): JsonResponse
    {
        if ($this->order->where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->first()) {
            $this->order->where(['user_id' => $request->user()->id, 'id' => $request['order_id']])->update([
                'payment_method' => $request['payment_method']
            ]);
            return response()->json(['message' => translate('Payment method is updated.')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('not found')]
            ]
        ], 401);
    }

    public function getReorderProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $details = $this->orderDetail
            ->where(['order_id' => $request['order_id']])
            ->get();

        if ($details->count() < 1) {
            return response()->json([
                'errors' => [['code' => 'order', 'message' => translate('Order not found!')]]], 404);
        }

        $details = Helpers::order_details_formatter($details);

        $orderProductIds = $this->orderDetail
            ->where(['order_id' => $request->order_id])
            ->pluck('product_id')
            ->toArray();

        $products = $this->product
            ->whereIn('id', $orderProductIds)
            ->latest()
            ->get();

        $products = Helpers::product_data_formatting($products, true);

        $data = [
            'order_details' => $details,
            'products' => $products
        ];

        return response()->json($data, 200);

    }
}
