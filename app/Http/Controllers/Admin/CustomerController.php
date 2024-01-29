<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Newsletter;
use App\Model\Order;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private Newsletter $newsletter,
        private Order $order,
        private User $user
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function customerList(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = $this->user->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $customers = $this->user;
        }

        $customers = $customers->with(['orders'])->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function view($id, Request $request): View|Factory|RedirectResponse|Application
    {
        $search = $request->search;
        $customer = $this->user->find($id);
        if (isset($customer)) {
            $orders = $this->order->latest()->where(['user_id' => $id])
                ->when($search, function ($query) use ($search) {
                    $key = explode(' ', $search);
                    foreach ($key as $value) {
                        $query->where('id', 'like', "%$value%");
                    }
                })
                ->paginate(Helpers::getPagination())
                ->appends(['search' => $search]);
            return view('admin-views.customer.customer-view', compact('customer', 'orders', 'search'));
        }
        Toastr::error(translate('Customer not found!'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function subscribedEmails(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $newsletters = $this->newsletter->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $newsletters = $this->newsletter;
        }

        $newsletters = $newsletters->latest()->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.customer.subscribed-list', compact('newsletters', 'search'));
    }
}
