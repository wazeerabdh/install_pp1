<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Traits\Payment;
use App\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
//    public function __construct(){
//        if (is_dir('App\Traits') && trait_exists('App\Traits\Payment')) {
//            $this->extendWithPaymentGatewayTrait();
//        }
//    }
//
//    private function extendWithPaymentGatewayTrait()
//    {
//        $extendedControllerClass = $this->generateExtendedControllerClass();
//        eval($extendedControllerClass);
//    }
//
//    private function generateExtendedControllerClass()
//    {
//        $baseControllerClass = get_class($this);
//        $traitClassName = 'App\Traits\Payment';
//
//        $extendedControllerClass = "
//            class ExtendedController extends $baseControllerClass {
//                use $traitClassName;
//            }
//        ";
//
//        return $extendedControllerClass;
//    }

    public function payment(Request $request)
    {

        if (!session()->has('payment_method')) {
            session()->put('payment_method', 'ssl_commerz');
        }

        $params = explode('&&', base64_decode($request['token']));
        foreach ($params as $param) {
            $data = explode('=', $param);
            if ($data[0] == 'customer_id') {
                session()->put('customer_id', $data[1]);
            } elseif ($data[0] == 'callback') {
                session()->put('callback', $data[1]);
            } elseif ($data[0] == 'order_amount') {
                session()->put('order_amount', $data[1]);
            }elseif ($data[0] == 'product_ids') {
                session()->put('product_ids', $data[1]);
            }
        }

        $order_amount = session('order_amount');
        $customer_id = session('customer_id');

        if (!isset($order_amount)) {
            return response()->json(['errors' => ['message' => 'Amount not found']], 403);
        }

        if (!$request->has('payment_method')) {
            return response()->json(['errors' => ['message' => 'Payment not found']], 403);
        }

        $additional_data = [
            'business_name' => Helpers::get_business_settings('restaurant_name') ?? '',
            'business_logo' => asset('storage/app/public/restaurant/' . Helpers::get_business_settings('logo'))
        ];

        $customer = User::find($customer_id);
        if (!isset($customer)) {
            return response()->json(['errors' => ['message' => 'Customer not found']], 403);
        }
        $customer = collect([
            'f_name' => $customer['f_name'],
            'l_name' => $customer['l_name'],
            'phone' => $customer['phone'] ?? '+8801100000000',
            'email' => $customer['email'] ?? 'test@mail.com',
        ]);

        $payer = new Payer($customer['f_name'] . ' ' . $customer['l_name'] , $customer['email'], $customer['phone'], '');

        $payment_info = new PaymentInfo(
            success_hook: 'order_place',
            failure_hook: 'order_cancel',
            currency_code: Helpers::currency_code(),
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: session('customer_id'),
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $order_amount,
            external_redirect_link: session('callback'),
            attribute: 'order',
            attribute_id: '10001'
        );

        $receiver_info = new Receiver('receiver_name','example.png');

        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        return redirect($redirect_link);
    }

    public function success()
    {
        if (session()->has('callback')) {
            return redirect(session('callback') . '/success');
        }
        return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function fail()
    {
        if (session()->has('callback')) {
            return redirect(session('callback') . '/fail');
        }
        return response()->json(['message' => 'Payment failed'], 403);
    }
}
