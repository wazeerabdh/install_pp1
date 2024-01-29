<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Carbon\Carbon;
use App\Model\Currency;
use App\Model\SocialMedia;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Model\BusinessSetting;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsController extends Controller
{
    public function __construct(
        private BusinessSetting $business_setting,
        private Currency        $currency,
        private SocialMedia     $social_media
    )
    {
    }

    /**
     * @return Application|Factory|View
     */
    public function BusinessSetup(): View|Factory|Application
    {
        if (!$this->business_setting->where(['key' => 'minimum_order_value'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'minimum_order_value'], [
                'value' => 1
            ]);
        }

        if (!$this->business_setting->where(['key' => 'fav_icon'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
                'value' => ''
            ]);
        }

        $logo = Helpers::get_business_settings('logo');
        $logo = Helpers::onErrorImage($logo, asset('storage/app/public/ecommerce') . '/' . $logo, asset('public/assets/admin/img/160x160/img2.jpg'), 'ecommerce/');

        $app_logo = Helpers::get_business_settings('app_logo');
        $app_logo = Helpers::onErrorImage($app_logo, asset('storage/app/public/ecommerce') . '/' . $app_logo, asset('public/assets/admin/img/160x160/img2.jpg'), 'ecommerce/');

        $fav_icon = Helpers::get_business_settings('fav_icon');
        $fav_icon = Helpers::onErrorImage($fav_icon, asset('storage/app/public/ecommerce') . '/' . $fav_icon, asset('public/assets/admin/img/160x160/img2.jpg'), 'ecommerce/');

        return view('admin-views.business-settings.restaurant-index', compact('logo', 'app_logo', 'fav_icon'));
    }

    /**
     * @param $side
     * @return JsonResponse
     */
    public function currencySymbolPosition($side): JsonResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'currency_symbol_position'], [
            'value' => $side
        ]);
        return response()->json(['message' => translate("Symbol position is ") . $side]);
    }

    /**
     * @return JsonResponse
     */
    public function maintenanceMode(): JsonResponse
    {
        $mode = Helpers::get_business_settings('maintenance_mode');
        DB::table('business_settings')->updateOrInsert(['key' => 'maintenance_mode'], [
            'value' => isset($mode) ? !$mode : 1
        ]);
        if (!$mode) {
            return response()->json(['message' => translate("Maintenance Mode is On.")]);
        }
        return response()->json(['message' => translate("Maintenance Mode is Off.")]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function BusinessSetupUpdate(Request $request): RedirectResponse
    {
        if ($request['email_verification'] == 1) {
            $request['phone_verification'] = 0;
        } elseif ($request['phone_verification'] == 1) {
            $request['email_verification'] = 0;
        }

        DB::table('business_settings')->updateOrInsert(['key' => 'country'], [
            'value' => $request['country']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'phone_verification'], [
            'value' => $request['phone_verification']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'email_verification'], [
            'value' => $request['email_verification']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'self_pickup'], [
            'value' => $request['self_pickup']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'currency'], [
            'value' => $request['currency']
        ]);

        $curr_logo = $this->business_setting->where(['key' => 'logo'])->first();
        DB::table('business_settings')->updateOrInsert(['key' => 'logo'], [
            'value' => $request->has('logo') ? Helpers::update('ecommerce/', $curr_logo['value'], 'png', $request->file('logo')) : $curr_logo['value']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'restaurant_name'], [
            'value' => $request->restaurant_name
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'phone'], [
            'value' => $request['phone']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'email_address'], [
            'value' => $request['email']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'address'], [
            'value' => $request['address']
        ]);


        DB::table('business_settings')->updateOrInsert(['key' => 'footer_text'], [
            'value' => $request['footer_text']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'minimum_order_value'], [
            'value' => $request['minimum_order_value']
        ]);

        $languages = $request['language'];

        if (in_array('en', $languages)) {
            unset($languages[array_search('en', $languages)]);
        }
        array_unshift($languages, 'en');

        DB::table('business_settings')->updateOrInsert(['key' => 'language'], [
            'value' => json_encode($languages),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'point_per_currency'], [
            'value' => $request['point_per_currency'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
            'value' => $request['time_zone'],
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
            'value' => $request['pagination_limit'],
        ]);


        DB::table('business_settings')->updateOrInsert(['key' => 'dm_self_registration'], [
            'value' => $request['dm_self_registration']
        ]);

        $curr_fav_icon = $this->business_setting->where(['key' => 'fav_icon'])->first();
        DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
            'value' => $request->has('fav_icon') ? Helpers::update('ecommerce/', $curr_fav_icon['value'], 'png', $request->file('fav_icon')) : $curr_fav_icon['value']
        ]);

        $curr_app_logo = $this->business_setting->where(['key' => 'app_logo'])->first();
        DB::table('business_settings')->updateOrInsert(['key' => 'app_logo'], [
            'value' => $request->has('app_logo') ? Helpers::update('ecommerce/', $curr_app_logo['value'], 'png', $request->file('app_logo')) : $curr_app_logo['value']
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function mailIndex(): View|Factory|Application
    {
        return view('admin-views.business-settings.mail-index');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function mailSend(Request $request): JsonResponse
    {
        $response_flag = 0;
        try {
            $emailServices = Helpers::get_business_settings('mail_config');

            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                Mail::to($request->email)->send(new \App\Mail\TestEmailSender());
                $response_flag = 1;
            }
        } catch (\Exception $exception) {
            $response_flag = 2;
        }

        return response()->json(['success' => $response_flag]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function mailConfig(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'mail_config'])->update([
            'value' => json_encode([
                "status" => 1,
                "name" => $request['name'],
                "host" => $request['host'],
                "driver" => $request['driver'],
                "port" => $request['port'],
                "username" => $request['username'],
                "email_id" => $request['email'],
                "encryption" => $request['encryption'],
                "password" => $request['password']
            ])
        ]);
        Toastr::success(translate('Configuration updated successfully!'));
        return back();
    }

    /**
     * @param $status
     * @return JsonResponse
     */
    public function mailConfigStatus($status): JsonResponse
    {
        $data = Helpers::get_business_settings('mail_config');
        $data['status'] = $status == '1' ? 1 : 0;

        $this->business_setting->where(['key' => 'mail_config'])->update([
            'value' => $data,
        ]);
        return response()->json(['message' => 'Mail config status updated']);
    }

    /**
     * @return Application|Factory|View
     */
    public function paymentIndex(): View|Factory|Application
    {
        $published_status = 0; // Set a default value
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $routes = config('addon_admin_routes');
        $desiredName = 'payment_setup';
        $payment_url = '';

        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $payment_url = $route['url'];
                    break 2;
                }
            }
        }

        $data_values = Setting::whereIn('settings_type', ['payment_config'])
            ->whereIn('key_name', ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paystack', 'paymob_accept', 'flutterwave', 'bkash', 'mercadopago'])
            ->get();

        return view('admin-views.business-settings.payment-index', compact('published_status', 'payment_url', 'data_values'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function paymentConfigUpdate(Request $request): RedirectResponse
    {
        $validation = [
            'gateway' => 'required|in:ssl_commerz,paypal,stripe,razor_pay,senang_pay,paystack,paymob_accept,flutterwave,bkash,mercadopago',
            'mode' => 'required|in:live,test'
        ];

        $request['status'] = $request->has('status') ? 1 : 0;

        $additional_data = [];

        if ($request['gateway'] == 'ssl_commerz') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'store_id' => 'required_if:status,1',
                'store_password' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paypal') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'client_id' => 'required_if:status,1',
                'client_secret' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'stripe') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'published_key' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'razor_pay') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'senang_pay') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paystack') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'public_key' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_email' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paymob_accept') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'api_key' => 'required_if:status,1',
                'iframe_id' => 'required_if:status,1',
                'integration_id' => 'required_if:status,1',
                'hmac' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'mercadopago') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'access_token' => 'required_if:status,1',
                'public_key' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'flutterwave') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'secret_key' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
                'hash' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'bkash') {
            $additional_data = [
                'status' => 'required|in:1,0',
                'app_key' => 'required_if:status,1',
                'app_secret' => 'required_if:status,1',
                'username' => 'required_if:status,1',
                'password' => 'required_if:status,1',
            ];
        }

        $request->validate(array_merge($validation, $additional_data));

        $settings = Setting::where('key_name', $request['gateway'])->where('settings_type', 'payment_config')->first();

        $additional_data_image = $settings['additional_data'] != null ? json_decode($settings['additional_data']) : null;

        if ($request->has('gateway_image')) {
            $gateway_image = Helpers::upload('payment_modules/gateway_image/', 'png', $request['gateway_image']);
        } else {
            $gateway_image = $additional_data_image != null ? $additional_data_image->gateway_image : '';
        }

        if ($request['gateway_title'] == null) {
            Toastr::error(translate('payment_gateway_title_is_required!'));
            return back();
        }

        $payment_additional_data = [
            'gateway_title' => $request['gateway_title'],
            'gateway_image' => $gateway_image,
        ];

        $validator = Validator::make($request->all(), array_merge($validation, $additional_data));

        Setting::updateOrCreate(['key_name' => $request['gateway'], 'settings_type' => 'payment_config'], [
            'key_name' => $request['gateway'],
            'live_values' => $validator->validate(),
            'test_values' => $validator->validate(),
            'settings_type' => 'payment_config',
            'mode' => $request['mode'],
            'is_active' => $request->status,
            'additional_data' => json_encode($payment_additional_data),
        ]);

        Toastr::success(GATEWAYS_DEFAULT_UPDATE_200['message']);
        return back();

    }

    /**
     * @param Request $request
     * @param $name
     * @return RedirectResponse
     */
    public function paymentUpdate(Request $request, $name): RedirectResponse
    {
        if ($name == 'cash_on_delivery') {
            $payment = $this->business_setting->where('key', 'cash_on_delivery')->first();
            if (!isset($payment)) {
                DB::table('business_settings')->insert([
                    'key' => 'cash_on_delivery',
                    'value' => json_encode([
                        'status' => $request['status']
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('business_settings')->where(['key' => 'cash_on_delivery'])->update([
                    'key' => 'cash_on_delivery',
                    'value' => json_encode([
                        'status' => $request['status']
                    ]),
                    'updated_at' => now()
                ]);
            }
        } elseif ($name == 'digital_payment') {
            $payment = $this->business_setting->where('key', 'digital_payment')->first();
            if (!isset($payment)) {
                DB::table('business_settings')->insert([
                    'key' => 'digital_payment',
                    'value' => json_encode([
                        'status' => $request['status']
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('business_settings')->where(['key' => 'digital_payment'])->update([
                    'key' => 'digital_payment',
                    'value' => json_encode([
                        'status' => $request['status']
                    ]),
                    'updated_at' => now()
                ]);
            }
        }

        Toastr::success(translate('payment settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function currencyStore(Request $request): RedirectResponse
    {
        $request->validate([
            'currency_code' => 'required|unique:currencies',
        ]);

        $this->currency->create([
            "country" => $request['country'],
            "currency_code" => $request['currency_code'],
            "currency_symbol" => $request['symbol'],
            "exchange_rate" => $request['exchange_rate'],
        ]);
        Toastr::success(translate('Currency added successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function currencyEdit($id): View|Factory|Application
    {
        $currency = $this->currency->find($id);
        return view('admin-views.business-settings.currency-update', compact('currency'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|RedirectResponse|Redirector
     */
    public function currencyUpdate(Request $request, $id): Redirector|RedirectResponse|Application
    {
        $this->currency->where(['id' => $id])->update([
            "country" => $request['country'],
            "currency_code" => $request['currency_code'],
            "currency_symbol" => $request['symbol'],
            "exchange_rate" => $request['exchange_rate'],
        ]);
        Toastr::success(translate('Currency updated successfully!'));
        return redirect('admin/business-settings/currency-add');
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function currencyDelete($id): RedirectResponse
    {
        $this->currency->where(['id' => $id])->delete();
        Toastr::success(translate('Currency removed successfully!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function termsAndConditions(): View|Factory|Application
    {
        $tnc = $this->business_setting->where(['key' => 'terms_and_conditions'])->first();
        if (!$tnc) {
            $this->business_setting->insert([
                'key' => 'terms_and_conditions',
                'value' => ''
            ]);
        }
        return view('admin-views.business-settings.terms-and-conditions', compact('tnc'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function termsAndConditionsUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'terms_and_conditions'])->update([
            'value' => $request->tnc
        ]);
        Toastr::success(translate('Terms and Conditions updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function privacyPolicy(): View|Factory|Application
    {
        $data = $this->business_setting->where(['key' => 'privacy_policy'])->first();
        if (!$data) {
            $data = [
                'key' => 'privacy_policy',
                'value' => '',
            ];
            $this->business_setting->insert($data);
        }
        return view('admin-views.business-settings.privacy-policy', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function privacyPolicyUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'privacy_policy'])->update([
            'value' => $request->privacy_policy,
        ]);

        Toastr::success(translate('Privacy policy updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function aboutUs(): View|Factory|Application
    {
        $data = $this->business_setting->where(['key' => 'about_us'])->first();
        if (!$data) {
            $data = [
                'key' => 'about_us',
                'value' => '',
            ];
            $this->business_setting->insert($data);
        }
        return view('admin-views.business-settings.about-us', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function aboutUsUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'about_us'])->update([
            'value' => $request->about_us,
        ]);

        Toastr::success(translate('About us updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function fcmIndex(): View|Factory|Application
    {
        if (!$this->business_setting->where(['key' => 'fcm_topic'])->first()) {
            $this->business_setting->insert([
                'key' => 'fcm_topic',
                'value' => ''
            ]);
        }
        if (!$this->business_setting->where(['key' => 'fcm_project_id'])->first()) {
            $this->business_setting->insert([
                'key' => 'fcm_project_id',
                'value' => ''
            ]);
        }
        if (!$this->business_setting->where(['key' => 'push_notification_key'])->first()) {
            $this->business_setting->insert([
                'key' => 'push_notification_key',
                'value' => ''
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_pending_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_pending_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_confirmation_msg'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_confirmation_msg',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_processing_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_processing_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'out_for_delivery_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'out_for_delivery_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_delivered_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_delivered_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_assign_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_assign_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_start_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_start_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_delivered_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_delivered_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => ''
                ])
            ]);
        }

        if (!$this->business_setting->where(['key' => 'customer_notify_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'customer_notify_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        return view('admin-views.business-settings.fcm-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateFcm(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'fcm_project_id'], [
            'value' => $request['fcm_project_id']
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'push_notification_key'], [
            'value' => $request['push_notification_key']
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateFcmMessages(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'order_pending_message'], [
            'value' => json_encode([
                'status' => $request['pending_status'] == 1 ? 1 : 0,
                'message' => $request['pending_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'order_confirmation_msg'], [
            'value' => json_encode([
                'status' => $request['confirm_status'] == 1 ? 1 : 0,
                'message' => $request['confirm_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'order_processing_message'], [
            'value' => json_encode([
                'status' => $request['processing_status'] == 1 ? 1 : 0,
                'message' => $request['processing_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'out_for_delivery_message'], [
            'value' => json_encode([
                'status' => $request['out_for_delivery_status'] == 1 ? 1 : 0,
                'message' => $request['out_for_delivery_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'order_delivered_message'], [
            'value' => json_encode([
                'status' => $request['delivered_status'] == 1 ? 1 : 0,
                'message' => $request['delivered_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'delivery_boy_assign_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_assign_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_assign_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'delivery_boy_start_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_start_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_start_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'delivery_boy_delivered_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_delivered_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_delivered_message']
            ])
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'customer_notify_message'], [
            'value' => json_encode([
                'status' => $request['customer_notify_status'] == 1 ? 1 : 0,
                'message' => $request['customer_notify_message'],
            ]),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'returned_message'], [
            'value' => json_encode([
                'status' => $request['returned_status'] == 1 ? 1 : 0,
                'message' => $request['returned_message'],
            ]),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'failed_message'], [
            'value' => json_encode([
                'status' => $request['failed_status'] == 1 ? 1 : 0,
                'message' => $request['failed_message'],
            ]),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'canceled_message'], [
            'value' => json_encode([
                'status' => $request['canceled_status'] == 1 ? 1 : 0,
                'message' => $request['canceled_message'],
            ]),
        ]);

        Toastr::success(translate('Message updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function mapApiSettings(): View|Factory|Application
    {
        return view('admin-views.business-settings.map-api');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateMapApi(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'map_api_key'], [
            'value' => $request->map_api_key,
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'map_api_server_key'], [
            'value' => $request['map_api_server_key'],
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function recaptchaIndex(Request $request): View|Factory|Application
    {
        return view('admin-views.business-settings.recaptcha-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function recaptchaUpdate(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'recaptcha'], [
            'key' => 'recaptcha',
            'value' => json_encode([
                'status' => $request['status'] == 'on' ? 1 : 0,
                'site_key' => $request['site_key'],
                'secret_key' => $request['secret_key']
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function returnPageIndex(Request $request): View|Factory|Application
    {
        $data = $this->business_setting->where(['key' => 'return_page'])->first();

        if (!$data) {
            $data = [
                'key' => 'return_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => null
                ]),
            ];
            $this->business_setting->insert($data);
        }
        return view('admin-views.business-settings.return_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function returnPageUpdate(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'return_page'], [
            'key' => 'return_page',
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request->has('content') ? $request['content'] : null
            ]),
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function refundPageIndex(Request $request): View|Factory|Application
    {
        $data = $this->business_setting->where(['key' => 'refund_page'])->first();

        if ($data == false) {
            $data = [
                'key' => 'refund_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => null
                ]),
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.refund_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function refundPageUpdate(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'refund_page'], [
            'key' => 'refund_page',
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request->has('content') ? $request['content'] : null
            ]),
            'created_at' => now(),
            'updated_at' => now(),

        ]);


        Toastr::success(translate('Updated Successfully'));
        return back();
    }


    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function cancellationPageIndex(Request $request): View|Factory|Application
    {
        $data = $this->business_setting->where(['key' => 'cancellation_page'])->first();

        if ($data == false) {
            $data = [
                'key' => 'cancellation_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => null
                ]),
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.cancellation_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancellationPageUpdate(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'cancellation_page'], [
            'key' => 'cancellation_page',
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request->has('content') ? $request['content'] : null
            ]),
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function appSettingIndex(): View|Factory|Application
    {
        return View('admin-views.business-settings.app-setting-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function appSettingUpdate(Request $request): RedirectResponse
    {
        if ($request->platform == 'android') {
            DB::table('business_settings')->updateOrInsert(['key' => 'play_store_config'], [
                'value' => json_encode([
                    'status' => $request['play_store_status'],
                    'link' => $request['play_store_link'],
                    'min_version' => $request['android_min_version'],

                ]),
            ]);

            Toastr::success(translate('Updated Successfully for Android'));
            return back();
        }

        if ($request->platform == 'ios') {
            DB::table('business_settings')->updateOrInsert(['key' => 'app_store_config'], [
                'value' => json_encode([
                    'status' => $request['app_store_status'],
                    'link' => $request['app_store_link'],
                    'min_version' => $request['ios_min_version'],
                ]),
            ]);

            Toastr::success(translate('Updated Successfully for IOS'));
            return back();
        }


        Toastr::error(translate('Updated failed'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function firebaseMessageConfigIndex(): View|Factory|Application
    {
        return View('admin-views.business-settings.firebase-config-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function firebaseMessageConfig(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'firebase_message_config'], [
            'key' => 'firebase_message_config',
            'value' => json_encode([
                'apiKey' => $request['apiKey'],
                'authDomain' => $request['authDomain'],
                'projectId' => $request['projectId'],
                'storageBucket' => $request['storageBucket'],
                'messagingSenderId' => $request['messagingSenderId'],
                'appId' => $request['appId'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        self::firebaseMessageConfigFileGenerate();

        Toastr::success(translate('Config Updated Successfully'));
        return back();
    }

    /**
     * @return void
     */
    function firebaseMessageConfigFileGenerate(): void
    {
        $config = \App\CentralLogics\Helpers::get_business_settings('firebase_message_config');
        $apiKey = $config['apiKey'] ?? '';
        $authDomain = $config['authDomain'] ?? '';
        $projectId = $config['projectId'] ?? '';
        $storageBucket = $config['storageBucket'] ?? '';
        $messagingSenderId = $config['messagingSenderId'] ?? '';
        $appId = $config['appId'] ?? '';

        try {
            $old_file = fopen("firebase-messaging-sw.js", "w") or die("Unable to open file!");

            $new_text = "importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');\n";
            $new_text .= "importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');\n";
            $new_text .= 'firebase.initializeApp({apiKey: "' . $apiKey . '",authDomain: "' . $authDomain . '",projectId: "' . $projectId . '",storageBucket: "' . $storageBucket . '", messagingSenderId: "' . $messagingSenderId . '", appId: "' . $appId . '"});';
            $new_text .= "\nconst messaging = firebase.messaging();\n";
            $new_text .= "messaging.setBackgroundMessageHandler(function (payload) { return self.registration.showNotification(payload.data.title, { body: payload.data.body ? payload.data.body : '', icon: payload.data.icon ? payload.data.icon : '' }); });";
            $new_text .= "\n";

            fwrite($old_file, $new_text);
            fclose($old_file);

        } catch (\Exception $exception) {
        }

    }

    /**
     * @return Application|Factory|View
     */
    public function socialMedia(): View|Factory|Application
    {
        return view('admin-views.business-settings.social-media');
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->social_media->orderBy('id', 'desc')->get();
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaStore(Request $request): JsonResponse
    {
        try {
            $this->social_media->updateOrInsert([
                'name' => $request->get('name'),
            ], [
                'name' => $request->get('name'),
                'link' => $request->get('link'),
            ]);

            return response()->json([
                'success' => 1,
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'error' => 1,
            ]);
        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaEdit(Request $request): JsonResponse
    {
        $data = $this->social_media->where('id', $request->id)->first();
        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaUpdate(Request $request): JsonResponse
    {
        $socialMedia = $this->social_media->find($request->id);
        $socialMedia->name = $request->name;
        $socialMedia->link = $request->link;
        $socialMedia->save();
        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaDelete(Request $request): JsonResponse
    {
        $socialMedia = $this->social_media->find($request->id);
        $socialMedia->delete();
        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaStatusUpdate(Request $request): JsonResponse
    {
        $socialMedia = $this->social_media->find($request->id);
        $socialMedia->status = $socialMedia->status == 1 ? 0 : 1;
        $socialMedia->save();

        return response()->json([
            'success' => 1,
        ], 200);
    }

    /**
     * @return Application|Factory|View
     */
    public function otpIndex(): Factory|View|Application
    {
        return view('admin-views.business-settings.otp-setup');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateOtp(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'maximum_otp_hit'], [
            'value' => $request['maximum_otp_hit'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'otp_resend_time'], [
            'value' => $request['otp_resend_time'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'temporary_block_time'], [
            'value' => $request['temporary_block_time'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'maximum_login_hit'], [
            'value' => $request['maximum_login_hit'],
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'temporary_login_block_time'], [
            'value' => $request['temporary_login_block_time'],
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function cookiesSetup(): Factory|View|Application
    {
        return view('admin-views.business-settings.cookies-setup');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cookiesSetupUpdate(Request $request): RedirectResponse
    {
        DB::table('business_settings')->updateOrInsert(['key' => 'cookies'], [
            'value' => json_encode([
                'status' => $request['status'],
                'text' => $request['text'],
            ])
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function deliveryFeeSetup(): Factory|View|Application
    {
        return view('admin-views.business-settings.delivery-fee');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function deliveryFeeSetupUpdate(Request $request): RedirectResponse
    {
        if ($request->delivery_charge == null) {
            $request->delivery_charge = $this->business_setting->where(['key' => 'delivery_charge'])->first()->value;
        }

        DB::table('business_settings')->updateOrInsert(['key' => 'delivery_charge'], [
            'value' => $request->delivery_charge
        ]);

        if ($request['min_shipping_charge'] == null) {
            $request['min_shipping_charge'] = Helpers::get_business_settings('delivery_management')['min_shipping_charge'];
        }
        if ($request['shipping_per_km'] == null) {
            $request['shipping_per_km'] = Helpers::get_business_settings('delivery_management')['shipping_per_km'];
        }
        if ($request['shipping_status'] == 1) {
            $request->validate([
                'min_shipping_charge' => 'required',
                'shipping_per_km' => 'required',
            ],
                [
                    'min_shipping_charge.required' => 'Minimum shipping charge is required while shipping method is active',
                    'shipping_per_km.required' => 'Shipping charge per Kilometer is required while shipping method is active',
                ]);
        }


        DB::table('business_settings')->updateOrInsert(['key' => 'delivery_management'], [
            'value' => json_encode([
                'status' => $request['shipping_status'],
                'min_shipping_charge' => $request['min_shipping_charge'],
                'shipping_per_km' => $request['shipping_per_km'],
            ]),
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function socialMediaLogin(): Factory|View|Application
    {
        return view('admin-views.business-settings.social-media-login');
    }

    /**
     * @param $medium
     * @param $status
     * @return JsonResponse
     */
    public function changeSocialLoginStatus($medium, $status): JsonResponse
    {
        if ($medium == 'google') {
            DB::table('business_settings')->updateOrInsert(['key' => 'google_social_login'], [
                'value' => $status
            ]);
        } elseif ($medium == 'facebook') {
            DB::table('business_settings')->updateOrInsert(['key' => 'facebook_social_login'], [
                'value' => $status
            ]);
        }
        return response()->json(['message' => 'Status updated']);
    }

    /**
     * @return Application|Factory|View
     */
    public function socialMediaChat(): Factory|View|Application
    {
        if (!$this->business_setting->where(['key' => 'whatsapp'])->first()) {
            $this->business_setting->insert([
                'key' => 'whatsapp',
                'value' => json_encode([
                    'status' => 0,
                    'number' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'telegram'])->first()) {
            $this->business_setting->insert([
                'key' => 'telegram',
                'value' => json_encode([
                    'status' => 0,
                    'user_name' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'messenger'])->first()) {
            $this->business_setting->insert([
                'key' => 'messenger',
                'value' => json_encode([
                    'status' => 0,
                    'user_name' => '',
                ]),
            ]);
        }
        return view('admin-views.business-settings.chat-index');
    }

    public function updateSocialMediaChat(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'whatsapp_number' => 'required_if:whatsapp_status,1',
            'telegram_user_name' => 'required_if:telegram_status,1',
            'messenger_user_name' => 'required_if:messenger_status,1',
        ], [
            'whatsapp_number.required_if' => 'The WhatsApp number is required when WhatsApp status is set to active.',
            'telegram_user_name.required_if' => 'The Telegram username is required when Telegram status is set to active.',
            'messenger_user_name.required_if' => 'The Messenger username is required when Messenger status is set to active.',
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'whatsapp'], [
            'value' => json_encode([
                'status' => $request['whatsapp_status'] == 1 ? 1 : 0,
                'number' => $request['whatsapp_number'],
            ]),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'telegram'], [
            'value' => json_encode([
                'status' => $request['telegram_status'] == 1 ? 1 : 0,
                'user_name' => $request['telegram_user_name'],
            ]),
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'messenger'], [
            'value' => json_encode([
                'status' => $request['messenger_status'] == 1 ? 1 : 0,
                'user_name' => $request['messenger_user_name'],
            ]),
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

}
