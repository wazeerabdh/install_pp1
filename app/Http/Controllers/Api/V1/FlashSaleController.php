<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\FlashSale;
use App\Model\FlashSaleProduct;
use App\Model\Product;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function __construct(
        private FlashSale        $flashSale,
        private FlashSaleProduct $flashSaleProduct,
        private Product          $product
    )
    {
    }

    public function getFlashSale(Request $request): \Illuminate\Http\JsonResponse
    {
        $sortBy = $request['sort_by'];
        $flashSale = $this->flashSale->active()->first();

        if (!isset($flashSale)) {
            $products = [
                'total_size' => null,
                'limit' => $request['limit'],
                'offset' => $request['offset'],
                'flash_sale' => $flashSale,
                'products' => []
            ];
            return response()->json($products, 200);

        }

        $productIds = $this->flashSaleProduct->with(['product'])
            ->whereHas('product', function ($q) {
                $q->active();
            })
            ->where(['flash_sale_id' => $flashSale->id])
            ->pluck('product_id')
            ->toArray();

        $paginator = $this->product->with(['rating'])
            ->when(isset($sortBy) && $sortBy == 'price_high_to_low', function ($query) {
                return $query->orderBy('price', 'desc');
            })
            ->when(isset($sortBy) && $sortBy == 'price_low_to_high', function ($query) {
                return $query->orderBy('price', 'asc');
            })
            ->latest()
            ->whereIn('id', $productIds)
            ->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $products = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'flash_sale' => $flashSale,
            'products' => $paginator->items()
        ];

        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);

    }
}
