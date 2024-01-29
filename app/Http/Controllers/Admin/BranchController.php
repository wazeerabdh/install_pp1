<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        private Branch $branch
    ){}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        return view('admin-views.branch.index');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function list(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $branches = $this->branch->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $branches = $this->branch;
        }
        $branches = $branches->orderBy('id', 'desc')->paginate(Helpers::getPagination())->appends($queryParam);
        return view('admin-views.branch.list', compact('branches','search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|max:255|unique:branches',
            'email' => 'required|max:255|unique:branches',
            'password' => 'required|min:8|max:255',
            'image' => 'required|max:255',
        ], [
            'name.required' => 'Name is required!',
        ]);

        if (!empty($request->file('image'))) {
            $image_name = Helpers::upload('branch/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $branch = $this->branch;
        $branch->name = $request->name;
        $branch->email = $request->email;
        $branch->longitude = $request->longitude;
        $branch->latitude = $request->latitude;
        $branch->coverage = $request->coverage ? $request->coverage : 0;
        $branch->address = $request->address;
        $branch->password = bcrypt($request->password);
        $branch->image = $image_name;
        $branch->phone = $request->number;
        $branch->save();
        Toastr::success(translate('Branch added successfully!'));
        return redirect()->route('admin.branch.list');
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $branch = $this->branch->find($id);
        return view('admin-views.branch.edit', compact('branch'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'unique:branches,email,'.$id.',id'],
            'name' => ['required', 'unique:branches,name,'.$id.',id']
        ], [
            'name.required' => 'Name is required!',
        ]);

        $branch = $this->branch->find($id);
        $branch->name = $request->name;
        $branch->email = $request->email;
        $branch->longitude = $request->longitude;
        $branch->latitude = $request->latitude;
        $branch->coverage = $request->coverage ? $request->coverage : 0;
        $branch->address = $request->address;
        $branch->phone = $request->number;
        $branch->image = $request->has('image') ? Helpers::update('branch/', $branch->image, 'png', $request->file('image')) : $branch->image;
        if ($request['password'] != null) {
            $branch->password = bcrypt($request->password);
        }
        $branch->save();
        Toastr::success(translate('Branch updated successfully!'));
        return redirect()->route('admin.branch.list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $branch = $this->branch->find($request->id);
        $branch->delete();
        Toastr::success(translate('Branch removed!'));
        return back();
    }
}
