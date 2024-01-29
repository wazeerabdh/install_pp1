<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Review;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function __construct(
        private Product $product,
        private Review $review
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $products = $this->product->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            })->pluck('id')->toArray();
            $reviews = $this->review->whereIn('product_id',$products);
            $queryParam = ['search' => $request['search']];
        }else{
            $reviews = $this->review;
        }

        $reviews = $reviews->with(['product','customer'])->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.reviews.list',compact('reviews', 'search'));
    }

}
