<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private Order $order,
        private OrderDetail $order_detail,
        private DeliveryMan $delivery_man,
        private Branch $branch,
        private Product $product,
    ){}

    /**
     * @return Application|Factory|View
     */
    public function orderIndex(): Factory|View|Application
    {
        if (!session()->has('from_date')) {
            session()->put('from_date', date('Y-m-01'));
            session()->put('to_date', date('Y-m-30'));
        }

        return view('admin-views.report.order-index');
    }

    /**
     * @return Application|Factory|View
     */
    public function earningIndex(): Factory|View|Application
    {
        if (!session()->has('from_date')) {
            session()->put('from_date', date('Y-m-01'));
            session()->put('to_date', date('Y-m-30'));
        }
        return view('admin-views.report.earning-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function setDate(Request $request): RedirectResponse
    {
        $fromDate = \Carbon\Carbon::parse($request['from'])->startOfDay();
        $toDate = Carbon::parse($request['to'])->endOfDay();

        session()->put('from_date', $fromDate);
        session()->put('to_date', $toDate);
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function driverReport(Request $request): Factory|View|Application
    {
        $deliverymanId = $request['delivery_man_id'];
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $deliverymen = $this->delivery_man->all();

        $orders = $this->order->with('delivery_man')
            ->where('order_status', 'delivered')
            ->whereNotNull('delivery_man_id')
            ->when((!is_null($deliverymanId) && $deliverymanId != 'all'), function ($query) use ($deliverymanId) {
                return $query->where('delivery_man_id', $deliverymanId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate(Helpers::pagination_limit());

        return view('admin-views.report.deliveryman-report-index', compact('deliverymen','orders', 'deliverymanId', 'startDate', 'endDate'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function productReport(Request $request): Factory|View|Application
    {
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $branchId = $request['branch_id'];
        $productId = $request['product_id'];

        $branches = $this->branch->all();
        $products = $this->product->all();

        $orders = $this->order->with(['branch', 'details'])
            ->where('order_status', 'delivered')
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->get();

        $data = [];
        $totalSold = 0;
        $totalQuantity = 0;

        foreach ($orders as $order) {
            foreach ($order->details as $detail) {
                if ($detail['product_id'] == $request['product_id']) {
                    $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
                    $orderTotal = $price * $detail['quantity'];
                    $data[] = [
                        'order_id' => $order['id'],
                        'date' => $order['created_at'],
                        'customer' => $order->customer,
                        'price' => $orderTotal,
                        'quantity' => $detail['quantity'],
                    ];
                    $totalSold += $orderTotal;
                    $totalQuantity += $detail['quantity'];
                }
            }
        }

        return view('admin-views.report.product-report', compact('data', 'totalSold', 'totalQuantity', 'branches', 'products', 'startDate', 'endDate', 'branchId', 'productId'));
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportProductReport(Request $request): StreamedResponse|string
    {
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $branchId = $request['branch_id'];

        $orders = $this->order->with(['branch', 'details'])
            ->where('order_status', 'delivered')
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->get();

        $data = [];

        foreach ($orders as $order) {
            foreach ($order->details as $detail) {
                if ($detail['product_id'] == $request['product_id']) {
                    $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
                    $orderTotal = $price * $detail['quantity'];
                    $data[] = [
                        'Order Id' => $order['id'],
                        'Date' => $order->created_at,
                        'Quantity' => $detail['quantity'],
                        'Amount' => $orderTotal,
                    ];
                }
            }
        }
        return (new FastExcel($data))->download('product-report.xlsx');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function saleReport(Request $request): Factory|View|Application
    {
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $branchId = $request['branch_id'];

        $branches = $this->branch->all();

        $orders = $this->order->with(['branch', 'details'])
            ->where('order_status', 'delivered')
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->pluck('id')->toArray();


        $data = [];
        $totalSold = 0;
        $totalQuantity = 0;

        $orderDetails = $this->order_detail->whereIn('order_id', $orders)->latest()->get();

        foreach ($orderDetails as $detail) {
            $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
            $orderTotal = $price * $detail['quantity'];
            $data[] = [
                'order_id' => $detail['order_id'],
                'date' => $detail['created_at'],
                'price' => $orderTotal,
                'quantity' => $detail['quantity'],
            ];
            $totalSold += $orderTotal;
            $totalQuantity += $detail['quantity'];
        }

        return view('admin-views.report.sale-report', compact('orders', 'data', 'totalSold', 'totalQuantity', 'startDate', 'endDate', 'branchId', 'branches'));
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportSaleReport(Request $request): StreamedResponse|string
    {
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];
        $branchId = $request['branch_id'];

        $orders = $this->order->with(['branch', 'details'])
            ->where('order_status', 'delivered')
            ->when((!is_null($branchId) && $branchId != 'all'), function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when((!is_null($startDate) && !is_null($endDate)), function ($query) use ($startDate, $endDate) {
                return $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->pluck('id')->toArray();

        $data = [];

        foreach ($this->order_detail->whereIn('order_id', $orders)->get() as $detail) {
            $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
            $orderTotal = $price * $detail['quantity'];
            $data[] = [
                'Order Id' => $detail['order_id'],
                'Date' => $detail['created_at'],
                'Quantity' => $detail['quantity'],
                'Price' => $orderTotal,
            ];
        }
        return (new FastExcel($data))->download('sale-report.xlsx');
    }
}
