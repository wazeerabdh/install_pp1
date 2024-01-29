<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Coupon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function __construct(
        private Coupon $coupon
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $coupons = $this->coupon->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%")
                        ->orWhere('code', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $coupons = $this->coupon;
        }

        $coupons = $coupons->orderBy('id', 'desc')->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.coupon.index', compact('coupons', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required',
            'title' => 'required',
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required'
        ]);

        if ($request->discount_type == 'amount' && $request->min_purchase < $request->discount){
            Toastr::error(translate('discount amount won’t be more than min purchase.'));
            return back();
        }

        DB::table('coupons')->insert([
            'title' => $request->title,
            'code' => $request->code,
            'limit' => $request->limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => $request->discount_type,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon added successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): Factory|View|Application
    {
        $coupon = $this->coupon->where(['id' => $id])->first();
        return view('admin-views.coupon.edit', compact('coupon'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'code' => 'required',
            'title' => 'required',
            'start_date' => 'required',
            'expire_date' => 'required',
            'discount' => 'required'
        ]);

        if ($request->discount_type == 'amount' && $request->min_purchase < $request->discount){
            Toastr::error(translate('discount amount won’t be more than min purchase.'));
            return back();
        }

        DB::table('coupons')->where(['id' => $id])->update([
            'title' => $request->title,
            'code' => $request->code,
            'limit' => $request->limit,
            'coupon_type' => $request->coupon_type,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'min_purchase' => $request->min_purchase != null ? $request->min_purchase : 0,
            'max_discount' => $request->max_discount != null ? $request->max_discount : 0,
            'discount' => $request->discount_type == 'amount' ? $request->discount : $request['discount'],
            'discount_type' => $request->discount_type,
            'updated_at' => now()
        ]);

        Toastr::success(translate('Coupon updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->status = $request->status;
        $coupon->save();
        Toastr::success(translate('Coupon status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $coupon = $this->coupon->find($request->id);
        $coupon->delete();
        Toastr::success(translate('Coupon removed!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $coupon = $this->coupon->find($request->id);

        return response()->json([
            'view' => view('admin-views.coupon.details', compact('coupon'))->render(),
        ]);
    }

}
