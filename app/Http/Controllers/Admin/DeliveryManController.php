<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use App\Model\DMReview;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DeliveryManController extends Controller
{
    public function __construct(
        private DeliveryMan $delivery_man,
        private DMReview $dm_review
    ){}

    /**
     * @return Application|Factory|View
     */
    public function index(): View|Factory|Application
    {
        return view('admin-views.delivery-man.index');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $deliveryMan = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $deliveryMan = $this->delivery_man;
        }

        $deliveryMan = $deliveryMan->latest()->where('application_status', 'approved')->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.delivery-man.list', compact('deliveryMan', 'search'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function reviewsList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $deliveryMan = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%");
                }
            })->pluck('id')->toArray();
            $reviews = $this->dm_review->whereIn('delivery_man_id', $deliveryMan);
            $queryParam = ['search' => $request['search']];
        }else{
            $reviews = $this->dm_review;
        }

        $reviews = $reviews->with(['delivery_man', 'customer'])->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.delivery-man.reviews-list', compact('reviews', 'search'));
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function preview($id): Factory|View|Application
    {
        $deliveryMan = $this->delivery_man->with(['reviews'])->where(['id' => $id])->first();
        $reviews = $this->dm_review->where(['delivery_man_id' => $id])->latest()->paginate(20);
        return view('admin-views.delivery-man.view', compact('deliveryMan', 'reviews'));
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function store(Request $request): Redirector|Application|RedirectResponse
    {
        $request->validate([
            'f_name' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i|unique:delivery_men',
            'phone' => 'required|unique:delivery_men',
            'password' => 'required|min:8',
            'password_confirmation' => 'required_with:password|same:password|min:8'
        ], [
            'f_name.required' => translate('First name is required!'),
            'email.required' => translate('Email is required!'),
            'email.unique' => translate('Email must be unique!'),
            'phone.required' => translate('Phone is required!'),
            'phone.unique' => translate('Phone must be unique!'),
        ]);

        if (!empty($request->file('image'))) {
            $image_name = Helpers::upload('delivery-man/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $id_img_names = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $id_img_names[] = $identity_image;
            }
            $identity_image = json_encode($id_img_names);
        } else {
            $identity_image = json_encode([]);
        }

        $dm = $this->delivery_man;
        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name;
        $dm->email = $request->email;
        $dm->phone = $request->phone;
        $dm->identity_number = $request->identity_number;
        $dm->identity_type = $request->identity_type;
        $dm->branch_id = $request->branch_id;
        $dm->identity_image = $identity_image;
        $dm->image = $image_name;
        $dm->password = bcrypt($request->password);
        $dm->application_status= 'approved';
        $dm->save();

        Toastr::success(translate('Delivery-man added successfully!'));
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $deliveryMan = $this->delivery_man->find($id);
        return view('admin-views.delivery-man.edit', compact('deliveryMan'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $deliveryMan = $this->delivery_man->find($request->id);
        $deliveryMan->status = $request->status;
        $deliveryMan->save();
        Toastr::success(translate('Delivery-man status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|RedirectResponse|Redirector
     */
    public function update(Request $request, $id): Redirector|RedirectResponse|Application
    {
        $request->validate([
            'f_name' => 'required',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
            'password_confirmation' => 'required_with:password|same:password'
        ], [
            'f_name.required' => 'First name is required!'
        ]);

        $deliveryMan = $this->delivery_man->find($id);

        if ($deliveryMan['email'] != $request['email']) {
            $request->validate([
                'email' => 'required|unique:delivery_men',
            ]);
        }

        if ($deliveryMan['phone'] != $request['phone']) {
            $request->validate([
                'phone' => 'required|unique:delivery_men',
            ]);
        }

        if (!empty($request->file('image'))) {
            $image_name = Helpers::update('delivery-man/', $deliveryMan->image, 'png', $request->file('image'));
        } else {
            $image_name = $deliveryMan['image'];
        }

        if (!empty($request->file('identity_image'))) {
            foreach (json_decode($deliveryMan['identity_image'], true) as $img) {
                if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                    Storage::disk('public')->delete('delivery-man/' . $img);
                }
            }
            $img_keeper = [];
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $img_keeper[] = $identity_image;
            }
            $identity_image = json_encode($img_keeper);
        } else {
            $identity_image = $deliveryMan['identity_image'];
        }
        $deliveryMan->f_name = $request->f_name;
        $deliveryMan->l_name = $request->l_name;
        $deliveryMan->email = $request->email;
        $deliveryMan->phone = $request->phone;
        $deliveryMan->identity_number = $request->identity_number;
        $deliveryMan->identity_type = $request->identity_type;
        $deliveryMan->branch_id = $request->branch_id;
        $deliveryMan->identity_image = $identity_image;
        $deliveryMan->image = $image_name;
        $deliveryMan->password = strlen($request->password) > 1 ? bcrypt($request->password) : $deliveryMan['password'];
        $deliveryMan->save();
        Toastr::success(translate('Delivery-man updated successfully'));
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $deliveryMan = $this->delivery_man->find($request->id);
        if (Storage::disk('public')->exists('delivery-man/' . $deliveryMan['image'])) {
            Storage::disk('public')->delete('delivery-man/' . $deliveryMan['image']);
        }

        foreach (json_decode($deliveryMan['identity_image'], true) as $img) {
            if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                Storage::disk('public')->delete('delivery-man/' . $img);
            }
        }

        $deliveryMan->delete();
        Toastr::success(translate('Delivery-man removed!'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function pendingList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $deliveryman = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $deliveryman = $this->delivery_man;
        }

        $deliveryman = $deliveryman->with('branch')
            ->where('application_status', 'pending')
            ->latest()->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.delivery-man.pending-list', compact('deliveryman','search'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function deniedList(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $deliveryman = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $deliveryman = $this->delivery_man;
        }
        $deliveryman = $deliveryman->with('branch')
            ->where('application_status', 'denied')
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($queryParam);

        return view('admin-views.delivery-man.denied-list', compact('deliveryman','search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateApplication(Request $request): RedirectResponse
    {
        $deliveryMan = $this->delivery_man->findOrFail($request->id);
        $deliveryMan->application_status = $request->status;
        $deliveryMan->save();

        try{
            $emailServices = Helpers::get_business_settings('mail_config');
            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                Mail::to($deliveryMan->email)->send(new \App\Mail\DMSelfRegistration($request->status, $deliveryMan->f_name.' '.$deliveryMan->l_name));
            }

        }catch(\Exception $ex){
            info($ex);
        }

        Toastr::success(translate('application_status_updated_successfully'));
        return back();
    }
}
