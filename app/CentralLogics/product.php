<?php

namespace App\CentralLogics;


use App\Model\Product;
use App\Model\Review;
use App\User;

class ProductLogic
{
    public static function get_product($id)
    {
        return Product::active()->withCount(['wishlist'])->with(['rating'])->where('id', $id)->first();
    }

    public static function get_latest_products($sort_by, $limit = 10, $offset = 1)
    {
        $limit = is_null($limit) ? 10 : $limit;
        $offset = is_null($offset) ? 1 : $offset;

        $paginator = Product::active()
            ->withCount(['wishlist'])
            ->with(['rating'])
            ->when($sort_by == 'price_high_to_low', function ($query){
                return $query->orderBy('price', 'desc');
            })
            ->when($sort_by == 'price_low_to_high', function ($query){
                return $query->orderBy('price', 'asc');
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_related_products($product_id)
    {
        $product = Product::find($product_id);
        return Product::active()->withCount(['wishlist'])->with(['rating'])->where('category_ids', $product->category_ids)
            ->where('id', '!=', $product->id)
            ->limit(10)
            ->get();
    }

    public static function search_products($name, $price_low, $price_high, $rating, $category_id, $sort_by, $limit = 10, $offset = 1)
    {
        $product_ids = [];
        if (isset($rating)){
            $product_ids = Product::active()
                ->with('reviews')
                ->whereHas('reviews', function ($q) use ($rating) {
                    $q->select('product_id')
                        ->groupBy('product_id')
                        ->havingRaw("AVG(rating) <= ?", [$rating]);
                })
                ->pluck('id')
                ->toArray();
        }

        $product_ids_for_category = [];
        if (isset($category_id)){
            foreach (json_decode($category_id, true) as $categoryId) {
                $product_ids = Product::active()
                    ->where(function ($query) use ($categoryId) {
                        $query->whereJsonContains('category_ids', ['id' => (string)$categoryId]);
                    })
                    ->pluck('id')
                    ->toArray();
                $product_ids_for_category = array_unique(array_merge($product_ids_for_category, $product_ids));
            }
        }

        $key = explode(' ', $name);
        $searched_products = Product::active()
            ->withCount(['wishlist'])
            ->with(['rating'])
            ->when($key, function ($query) use ($key) {
                $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->when(isset($sort_by) && $sort_by == 'new_arrival', function ($query) use ($sort_by) {
                return $query->where('created_at', '>=', now()->subMonths(3));
            })
            ->when(isset($sort_by) && $sort_by == 'offer_product', function ($query) use ($sort_by) {
                return $query->where('discount', '>', 0);
            })
            ->when(($price_low != null && $price_high != null), function ($query) use ($price_low, $price_high) {
                return $query->whereBetween('price',[$price_low, $price_high]);
            })
            ->when(isset($category_id), function ($query) use ($product_ids_for_category) {
                $query->whereIn('id', $product_ids_for_category);
            })
            ->when(isset($rating), function ($query) use ($product_ids) {
                $query->whereIn('id', $product_ids);
            });

        $lowest_price = $price_low ?? $searched_products->min('price');
        $highest_price = $price_high ?? $searched_products->max('price');

        $paginator = $searched_products->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'lowest_price' => (int) $lowest_price ?? 0,
            'highest_price' => (int) $highest_price ?? 0,
            'products' => $paginator->items()
        ];
    }

    public static function get_product_review($id)
    {
        $reviews = Review::where('product_id', $id)->get();
        return $reviews;
    }

    public static function get_rating($reviews)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_overall_rating($reviews)
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }

    public static function get_favorite_products($limit, $offset, $user_id)
    {
        $limit = is_null($limit) ? 10 : $limit;
        $offset = is_null($offset) ? 1 : $offset;

        $ids = User::with('wishlist_products')->find($user_id)->wishlist_products->pluck('product_id')->toArray();
        $wishlist_products = Product::whereIn('id', $ids)->paginate($limit, ['*'], 'page', $offset);

        $formatted_products = Helpers::product_data_formatting($wishlist_products, true);

        return [
            'total_size' => $wishlist_products->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $formatted_products
        ];
    }

    public static function get_new_arrival_products($limit = 10, $offset = 1)
    {
        $threeMonthsAgo = now()->subMonths(3);

        $paginator = Product::active()
            ->withCount(['wishlist'])
            ->with(['rating'])
            ->where('created_at', '>=', $threeMonthsAgo)
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
    }
}
