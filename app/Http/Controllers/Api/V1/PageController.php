<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Model\BusinessSetting;

class PageController extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $returnPage = $this->businessSetting->where(['key' => 'return_page'])->first();
        $refundPage = $this->businessSetting->where(['key' => 'refund_page'])->first();
        $cancellationPage = $this->businessSetting->where(['key' => 'cancellation_page'])->first();

        return response()->json([
            'return_page' => isset($returnPage) ? json_decode($returnPage->value, true) : null,
            'refund_page' => isset($refundPage) ? json_decode($refundPage->value, true) : null,
            'cancellation_page' => isset($cancellationPage) ? json_decode($cancellationPage->value, true) : null,
        ]);
    }

}
