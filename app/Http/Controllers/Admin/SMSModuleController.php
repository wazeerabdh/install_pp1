<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SMSModuleController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function smsIndex(): Factory|View|Application
    {
        $publishedStatus = 0;
        $paymentPublishedStatus = config('get_payment_publish_status');
        if (isset($paymentPublishedStatus[0]['is_published'])) {
            $publishedStatus = $paymentPublishedStatus[0]['is_published'];
        }

        $routes = config('addon_admin_routes');
        $desiredName = 'sms_setup';
        $paymentUrl = '';

        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $paymentUrl = $route['url'];
                    break 2;
                }
            }
        }

        $dataValues= Setting::where('settings_type','sms_config')->whereIn('key_name', ['twilio','nexmo','2factor','msg91'])->get() ?? [];

        return view('admin-views.business-settings.sms-index',  compact('publishedStatus', 'paymentUrl', 'dataValues'));

    }

    /**
     * @param Request $request
     * @param $module
     * @return RedirectResponse
     */
    public function smsUpdate(Request $request, $module): RedirectResponse
    {
        $validation = [
            'gateway' => 'required|in:twilio,nexmo,2factor,msg91',
        ];

        $validationData = [];
        if ($module == 'twilio') {
            $validationData = [
                'status' => 'required|in:1,0',
                'sid' => 'required_if:status,1',
                'messaging_service_sid' => 'required_if:status,1',
                'token' => 'required_if:status,1',
                'from' => 'required_if:status,1',
                'otp_template' => 'required_if:status,1'
            ];
        } elseif ($module == 'nexmo') {
            $validationData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1',
                'token' => 'required_if:status,1',
                'from' => 'required_if:status,1',
                'otp_template' => 'required_if:status,1'
            ];
        } elseif ($module == '2factor') {
            $validationData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1'
            ];
        } elseif ($module == 'msg91') {
            $validationData = [
                'status' => 'required|in:1,0',
                'template_id' => 'required_if:status,1',
                'auth_key' => 'required_if:status,1',
            ];
        }
        $validation = $request->validate(array_merge($validation, $validationData));

        $additionalData = [];
        if ($module == 'twilio') {
            $additionalData = [
                'status' => $request['status'],
                'sid' => $request['sid'],
                'messaging_service_sid' => $request['messaging_service_sid'],
                'token' => $request['token'],
                'from' => $request['from'],
                'otp_template' => $request['otp_template'],
            ];

        } elseif ($module == 'nexmo') {
            $additionalData = [
                'status' =>$request['status'],
                'api_key' => $request['api_key'],
                'api_secret' => $request['api_secret'],
                'token' => $request['token'],
                'from' => $request['from'],
                'otp_template' => $request['otp_template'],
            ];

        } elseif ($module == '2factor') {
            $additionalData = [
                'status' => $request['status'],
                'api_key' => $request['api_key'],
            ];
        } elseif ($module == 'msg91') {
            $additionalData = [
                'status' => $request['status'],
                'template_id' => $request['template_id'],
                'auth_key' => $request['auth_key'],
            ];
        }

        $data= [
            'gateway' => $module ,
            'mode' =>  isset($request['status']) == 1  ?  'live': 'test'
        ];

        $credentials= json_encode(array_merge($data, $additionalData));
        DB::table('addon_settings')->updateOrInsert(['key_name' => $module, 'settings_type' => 'sms_config'], [
            'key_name' => $module,
            'live_values' => $credentials,
            'test_values' => $credentials,
            'settings_type' => 'sms_config',
            'mode' => isset($request['status']) == 1  ?  'live': 'test',
            'is_active' => isset($request['status']) == 1  ?  1: 0 ,
        ]);

        $smsGatewayArray = [
            'twilio','nexmo','2factor','msg91'
        ];

        if ($request['status'] == 1) {
            foreach ($smsGatewayArray as $gateway) {
                if ($module != $gateway) {
                    $keep = Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->first();
                    if (isset($keep)) {
                        $hold = $keep->live_values;
                        $hold['status'] = 0;
                        Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->update([
                            'live_values' => $hold,
                            'test_values' => $hold,
                            'is_active' => 0,
                        ]);
                    }
                }
            }
        }
        return back();
    }
}
