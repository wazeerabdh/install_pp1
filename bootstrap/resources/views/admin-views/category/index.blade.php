@extends('layouts.admin.app')

@section('title', translate('Add new category'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/brand-setup.png')}}" alt="{{ translate('image') }}">
                {{translate('category_Setup')}}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.category.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($language = \App\Model\BusinessSetting::where('key', 'language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'en')
                    @if ($language)
                    @php($default_lang = json_decode($language)[0])
                    <ul class="nav nav-tabs mb-4 max-content">
                        @foreach (json_decode($language) as $lang)
                            <li class="nav-item">
                                <a class="nav-link lang_link {{ $lang == $default_lang ? 'active' : '' }}" href="#"
                                    id="{{ $lang }}-link">{{ Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="row">
                        <div class="col-12">
                            @foreach (json_decode($language) as $lang)
                                <div class="form-group {{ $lang != $default_lang ? 'd-none' : '' }} lang_form"  id="{{ $lang }}-form">
                                    <label class="input-label">{{ translate('name') }} ({{ strtoupper($lang) }})</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{ translate('New Category') }}" maxlength="255"
                                            {{ $lang == $default_lang ? 'required' : '' }} oninvalid="document.getElementById('en-link').click()">
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach
                            @else
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group lang_form" id="{{ $default_lang }}-form">
                                        <label class="input-label">{{ translate('name') }} ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="name[]" class="form-control" maxlength="255"
                                                placeholder="{{ translate('New Category') }}" required>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $default_lang }}">
                                    @endif
                                    <input name="position" value="0" class="d-none">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="mb-2">{{translate('Image')}}</label>
                                        <div class="custom_upload_input ratio-1 max-w-200">
                                            <input type="file" name="image" class="custom-upload-input-file meta-img h-100" id="" data-imgpreview="pre_meta_image_viewer"
                                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2">
                                                <img id="pre_meta_image_viewer" class="h-auto aspect-1 bg-white ratio-1" src="img" onerror="this.classList.add('d-none')">
                                            </div>
                                            <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                    <h3 class="text-muted">{{ translate('Drag & Drop here') }}</h3>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="fs-16 mb-2 text-dark mt-2">{{ translate('Images Ratio') }} 1:1</p>
                                        <p class="fs-14 text-muted mb-0">{{ translate('Image format : jpg, png, jpeg | Maximum Size') }} : 2 MB</p>
                                    </div>
                                </div>
                                <div class="col-md-8">

                                    <div class="form-group">
                                        <label class="mb-2">{{translate('Banner Image')}}</label>
                                        <div class="custom_upload_input max-h200px ratio-8">
                                            <input type="file" name="banner_image" class="custom-upload-input-file meta-img" id="" data-imgpreview="pre_meta_image_viewer"
                                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-none">
                                                <i class="tio-delete"></i>
                                            </span>

                                            <div class="img_area_with_preview position-absolute z-index-2">
                                                <img id="pre_meta_image_viewer" class="aspect-1 bg-white" src="img" onerror="this.classList.add('d-none')">
                                            </div>
                                            <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center">
                                                <div class="d-flex flex-column justify-content-center align-items-center overflow-hidden">
                                                    <h3 class="text-muted">{{ translate('Drag & Drop here') }}</h3>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="fs-16 mb-2 text-dark mt-2">{{ translate('Banner Images Ratio') }} 8:1</p>
                                        <p class="fs-14 text-muted mb-0">{{ translate('Image format : jpg, png, jpeg | Maximum Size') }} : 2 MB</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary px-5">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="px-20 py-3">
                <div class="row gy-2 align-items-center">
                    <div class="col-lg-8 col-sm-4 col-md-6">
                        <h5 class="text-capitalize d-flex align-items-center gap-2 mb-0">
                            {{translate('Category Table')}}
                            <span class="badge badge-soft-dark rounded-50 fz-12">{{ $categories->total() }}</span>
                        </h5>
                    </div>
                    <div class="col-lg-4 col-sm-8 col-md-6">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search"
                                       class="form-control"
                                       placeholder="{{translate('Search by Category')}}" aria-label="Search"
                                       value="{{$search}}" required autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{translate('search')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Category_Image')}}</th>
                            <th>{{translate('name')}}</th>
                            <th>{{translate('Is Featured')}} ? <i class="tio-info-outined cursor-pointer" data-toggle="tooltip" title="{{ translate('If enable, the category will show in featured category') }}"></i></th>
                            <th>{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($categories as $key=>$category)
                        <tr>
                            <td>{{$categories->firstItem()+$key}}</td>
                            <td>
                                <div class="avatar-lg rounded border">
                                    <img class="img-fit rounded"
                                         src="{{$category['image_fullpath']}}"
                                         alt="{{ translate('image') }}">
                                </div>
                            </td>
                            <td>{{$category['name']}}</td>
                            <td>
                                <label class="on-off-toggle">
                                    <input class="on-off-toggle__input change-status" type="checkbox"
                                        {{$category['is_featured']==1? 'checked' : ''}}
                                        data-route="{{route('admin.category.featured',[$category['id'], $category->is_featured == 1 ? 0: 1])}}">
                                    <span class="on-off-toggle__slider"></span>
                                </label>
                            </td>
                            <td>
                                @if($category['status']==1)
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input change-status" {{$category['status']==1? 'checked' : ''}}
                                                id="{{$category['id']}}"
                                               data-route="{{route('admin.category.status',[$category['id'],0])}}">
                                        <span class="switcher_control"></span>
                                    </label>
                                @else
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input change-status" {{$category['status']==1? 'checked' : ''}}
                                                id="{{$category['id']}}"
                                               data-route="{{route('admin.category.status',[$category['id'],1])}}">
                                        <span class="switcher_control"></span>
                                    </label>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="btn btn-outline-info square-btn" href="{{route('admin.category.edit',[$category['id']])}}">
                                        <i class="tio tio-edit"></i>
                                    </a>
                                    <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                       data-id="category-{{$category['id']}}"
                                       data-message="{{translate('Want to delete this ?')}}">
                                        <i class="tio tio-delete"></i>
                                    </a>
                                </div>
                                <form action="{{route('admin.category.delete',[$category['id']])}}"
                                        method="post" id="category-{{$category['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $categories->links() !!}
                </div>
            </div>
            @if(count($categories)==0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="{{ translate('Image Description') }}">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/image-upload.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/category.js') }}"></script>
@endpush
