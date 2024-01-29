@extends('layouts.admin.app')

@section('title', translate('Product List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="{{ translate('product') }}">
                {{translate('product_list')}}
            </h2>
            <span class="badge badge-soft-dark rounded-50 fs-14">{{$products->total()}}</span>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="px-20 py-3">
                        <div class="row gy-2 align-items-center">
                            <div class="col-lg-4 col-sm-8 col-md-6">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="{{translate('Search by Product Name')}}" aria-label="Search"
                                               value="{{$search}}" required autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('search')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-8 col-sm-4 col-md-6 d-flex justify-content-sm-end">
                                <a href="{{route('admin.product.add-new')}}" class="btn btn-primary">
                                    <i class="tio-add"></i>
                                    {{translate('add_new_product')}}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('product_name')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('price')}}</th>
                                    <th>{{translate('stock')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($products as $key=>$product)
                                <tr>
                                    <td>{{$products->firstitem()+$key}}</td>
                                    <td>
                                        <div class="media gap-3 align-items-center">
                                            <div class="avatar rounded border">
                                                <img
                                                    src="{{$product['image_fullpath'][0]}}"
                                                     class="img-fit rounded"
                                                    alt="{{ translate('product') }}">
                                            </div>
                                             <a href="{{route('admin.product.view',[$product['id']])}}" class="media-body text-dark">
                                               {{substr($product['name'],0,20)}}{{strlen($product['name'])>20?'...':''}}
                                             </a>
                                        </div>
                                    </td>
                                    <td>
                                        @if($product['status']==1)
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input change-status" checked id="{{$product['id']}}"
                                                       data-route="{{route('admin.product.status',[$product['id'],0])}}">
                                                <span class="switcher_control"></span>
                                            </label>
                                        @else
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input change-status"  id="{{$product['id']}}"
                                                       data-route="{{route('admin.product.status',[$product['id'],1])}}">
                                                <span class="switcher_control"></span>
                                            </label>
                                        @endif
                                    </td>
                                    <td>{{ Helpers::set_symbol($product['price']) }}</td>
                                    <td>
                                        <label class="badge badge-soft-info fs-14">{{$product['total_stock']}}</label>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a class="btn btn-outline-primary square-btn"
                                                href="{{route('admin.product.edit',[$product['id']])}}">
                                                <i class="tio tio-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                               data-id="product-{{$product['id']}}"
                                               data-message="{{translate('Want to delete this product ?')}}">
                                                <i class="tio tio-delete"></i>
                                            </a>
                                        </div>
                                        <form action="{{route('admin.product.delete',[$product['id']])}}"
                                                method="post" id="product-{{$product['id']}}">
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
                            {!! $products->links() !!}
                        </div>
                    </div>
                    @if(count($products)==0)
                        <div class="text-center p-4">
                            <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                            <p class="mb-0">{{ translate('No data to show') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

