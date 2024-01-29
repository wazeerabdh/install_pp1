<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Attribute;
use App\Model\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function __construct(
        private Attribute $attribute,
        private Translation $translation
    ) {}

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    function index(Request $request): View|Factory|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $attributes = $this->attribute->where(function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->orWhere('name', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        }else{
            $attributes = $this->attribute;
        }

        $attributes = $attributes->orderBy('name')->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.attribute.index', compact('attributes', 'search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:attributes',
        ]);

        foreach ($request->name as $name) {
            if (strlen($name) > 255) {
                toastr::error(translate('Name is too long!'));
                return back();
            }
        }

        $attribute = new Attribute;
        $attribute->name = $request->name[array_search('en', $request->lang)];
        $attribute->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                $data[] = array(
                    'translationable_type' => 'App\Model\Attribute',
                    'translationable_id' => $attribute->id,
                    'locale' => $key,
                    'key' => 'name',
                    'value' => $request->name[$index],
                );
            }
        }
        if(count($data))
        {
            $this->translation->insert($data);
        }
        Toastr::success(translate('Attribute added successfully!'));
        return back();
    }

    /**
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $attribute = $this->attribute->withoutGlobalScopes()->with('translations')->find($id);
        return view('admin-views.attribute.edit', compact('attribute'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
        ]);

        foreach ($request->name as $name) {
            if (strlen($name) > 255) {
                toastr::error(translate('Name is too long!'));
                return back();
            }
        }

        $attribute = $this->attribute->find($id);
        $attribute->name = $request->name[array_search('en', $request->lang)];
        $attribute->save();

        foreach($request->lang as $index=>$key)
        {
            if($request->name[$index] && $key != 'en')
            {
                $this->translation->updateOrInsert(
                    ['translationable_type'  => 'App\Model\Attribute',
                        'translationable_id'    => $attribute->id,
                        'locale'                => $key,
                        'key'                   => 'name'],
                    ['value'                 => $request->name[$index]]
                );
            }
        }
        Toastr::success(translate('Attribute updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $attribute = $this->attribute->find($request->id);
        $attribute->delete();
        Toastr::success(translate('Attribute removed!'));
        return back();
    }
}
