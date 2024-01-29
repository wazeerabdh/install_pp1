<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Banner;

class BannerController extends Controller
{
    public function __construct(
        private Banner $banner
    )
    {
    }

    public function getBanners(): \Illuminate\Http\JsonResponse
    {
        try {
            return response()->json($this->banner->active()->get(), 200);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }
}
