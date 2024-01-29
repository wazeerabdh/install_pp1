<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\SocialMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfigController extends Controller
{
    public function configuration(): \Illuminate\Http\JsonResponse
    {
        $publishedStatus = 0;
        $paymentPublishedStatus = config('get_payment_publish_status');
        if (isset($paymentPublishedStatus[0]['is_published'])) {
            $publishedStatus = $paymentPublishedStatus[0]['is_published'];
        }
        $activeAddonPaymentList = $publishedStatus == 1 ? $this->getPaymentMethods() : $this->getDefaultPaymentMethods();
        $digitalPaymentStatus = BusinessSetting::where(['key' => 'digital_payment'])->first()->value;
        $digitalPaymentStatusValue = json_decode($digitalPaymentStatus, true);


        $currencySymbol = Currency::where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        $cod = json_decode(BusinessSetting::where(['key' => 'cash_on_delivery'])->first()->value, true);
        $dp = json_decode(BusinessSetting::where(['key' => 'digital_payment'])->first()->value, true);

        $deliveryManConfig = Helpers::get_business_settings('delivery_management');
        $deliveryManagement = array(
            "status" => (int)$deliveryManConfig['status'],
            "min_shipping_charge" => (float)$deliveryManConfig['min_shipping_charge'],
            "shipping_per_km" => (float)$deliveryManConfig['shipping_per_km'],
        );

        $cookiesConfig = Helpers::get_business_settings('cookies');
        $cookiesManagement = array(
            "status" => (int)$cookiesConfig['status'],
            "text" => $cookiesConfig['text'],
        );

        return response()->json([
            'ecommerce_name' => BusinessSetting::where(['key' => 'restaurant_name'])->first()->value,
            'ecommerce_logo' => BusinessSetting::where(['key' => 'logo'])->first()->value,
            'app_logo' => BusinessSetting::where(['key' => 'app_logo'])->first()->value,
            'ecommerce_address' => BusinessSetting::where(['key' => 'address'])->first()->value,
            'ecommerce_phone' => BusinessSetting::where(['key' => 'phone'])->first()->value,
            'ecommerce_email' => BusinessSetting::where(['key' => 'email_address'])->first()->value,
            'ecommerce_location_coverage' => Branch::where(['id' => 1])->first(['longitude', 'latitude', 'coverage']),
            'minimum_order_value' => (float)BusinessSetting::where(['key' => 'minimum_order_value'])->first()->value,
            'self_pickup' => (int)BusinessSetting::where(['key' => 'self_pickup'])->first()->value,
            'base_urls' => [
                'product_image_url' => asset('storage/app/public/product'),
                'customer_image_url' => asset('storage/app/public/profile'),
                'banner_image_url' => asset('storage/app/public/banner'),
                'category_image_url' => asset('storage/app/public/category'),
                'category_banner_image_url' => asset('storage/app/public/category/banner'),
                'review_image_url' => asset('storage/app/public/review'),
                'notification_image_url' => asset('storage/app/public/notification'),
                'ecommerce_image_url' => asset('storage/app/public/ecommerce'),
                'delivery_man_image_url' => asset('storage/app/public/delivery-man'),
                'chat_image_url' => asset('storage/app/public/conversation'),
                'flash_sale_image_url' => asset('storage/app/public/flash-sale'),
                'gateway_image_url' => asset('storage/app/public/payment_modules/gateway_image'),
            ],
            'currency_symbol' => $currencySymbol,
            'delivery_charge' => (float)BusinessSetting::where(['key' => 'delivery_charge'])->first()->value,
            'delivery_management' => $deliveryManagement,
            'cash_on_delivery' => $cod['status'] == 1 ? 'true' : 'false',
            'digital_payment' => $dp['status'] == 1 ? 'true' : 'false',
            'branches' => Branch::all(['id', 'name', 'email', 'longitude', 'latitude', 'address', 'coverage']),
            'terms_and_conditions' => BusinessSetting::where(['key' => 'terms_and_conditions'])->first()->value,
            'privacy_policy' => BusinessSetting::where(['key' => 'privacy_policy'])->first()->value,
            'about_us' => BusinessSetting::where(['key' => 'about_us'])->first()->value,
            'email_verification' => (boolean)Helpers::get_business_settings('email_verification') ?? 0,
            'phone_verification' => (boolean)Helpers::get_business_settings('phone_verification') ?? 0,
            'currency_symbol_position' => Helpers::get_business_settings('currency_symbol_position') ?? 'right',
            'maintenance_mode' => (boolean)Helpers::get_business_settings('maintenance_mode') ?? 0,
            'country' => Helpers::get_business_settings('country') ?? 'BD',
            'play_store_config' => [
                "status" => (boolean)Helpers::get_business_settings('play_store_config')['status'],
                "link" => Helpers::get_business_settings('play_store_config')['link'],
                "min_version" => Helpers::get_business_settings('play_store_config')['min_version'],
            ],
            'app_store_config' => [
                "status" => (boolean)Helpers::get_business_settings('app_store_config')['status'],
                "link" => Helpers::get_business_settings('app_store_config')['link'],
                "min_version" => Helpers::get_business_settings('app_store_config')['min_version'],
            ],
            'social_media_link' => SocialMedia::orderBy('id', 'desc')->active()->get(),
            'software_version' => (string)env('SOFTWARE_VERSION') ?? null,
            'footer_text' => Helpers::get_business_settings('footer_text'),
            'dm_self_registration' => (int)Helpers::get_business_settings('dm_self_registration'),
            'otp_resend_time' => Helpers::get_business_settings('otp_resend_time') ?? 60,
            'cookies_management' => $cookiesManagement,
            'social_login' => [
                'google' => (integer)BusinessSetting::where(['key' => 'google_social_login'])->first()->value,
                'facebook' => (integer)BusinessSetting::where(['key' => 'facebook_social_login'])->first()->value,
            ],
            'whatsapp' => json_decode(BusinessSetting::where(['key' => 'whatsapp'])->first()->value, true),
            'telegram' => json_decode(BusinessSetting::where(['key' => 'telegram'])->first()->value, true),
            'messenger' => json_decode(BusinessSetting::where(['key' => 'messenger'])->first()->value, true),
            'digital_payment_status' => (integer)$digitalPaymentStatusValue['status'],
            'active_payment_method_list' => (integer)$digitalPaymentStatusValue['status'] == 1 ? $activeAddonPaymentList : [],
        ]);
    }

    private function getPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData?->gateway_title,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }

    private function getDefaultPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')
            ->whereIn('settings_type', ['payment_config'])
            ->whereIn('key_name', ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paystack', 'paymob_accept', 'flutterwave', 'bkash', 'mercadopago'])
            ->get();

        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData?->gateway_title,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }
}
