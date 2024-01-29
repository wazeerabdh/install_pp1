@extends('layouts.admin.app')

@section('title', translate('Review List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/product-review.png')}}" alt="{{ translate('product-review') }}">
                {{translate('review_list')}}
            </h2>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="px-20 py-3">
                        <div class="row gy-2 align-items-center">
                            <div class="col-sm-4">
                                <h5 class="text-capitalize d-flex align-items-center gap-2 mb-0">
                                    {{translate('review_table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $reviews->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8">
                                <div class="d-flex flex-wrap justify-content-sm-end gap-2">
                                    <form action="#" method="GET">
                                        <div class="input-group">
                                            <input id="datatableSearch_" type="search" name="search"
                                                class="form-control"
                                                placeholder="{{translate('Search by Product Name')}}" aria-label="Search"
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
                    </div>

                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('product')}}</th>
                                <th>{{translate('customer')}}</th>
                                <th>{{translate('review')}}</th>
                                <th class="text-center">{{translate('rating')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($reviews as $key=>$review)
                                    <tr>
                                        <td>{{$reviews->firstitem()+$key}}</td>
                                        <td>
                                             @if($review->product)
                                                <a class="text-dark" href="{{route('admin.product.view',[$review['product_id']])}}">
                                                    {{ $review->product['name'] }}
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    {{translate('Product unavailable')}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($review->customer))
                                                <a class="text-dark" href="{{route('admin.customer.view',[$review->user_id])}}">
                                                    {{$review->customer->f_name." ".$review->customer->l_name}}
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    {{translate('customer_unavailable')}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="mx-w300 mn-w200 text-wrap">
                                                {{$review->comment}}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <label class="badge badge-soft-info d-flex gap-1 align-items-center justify-content-center">
                                                    {{$review->rating}} <i class="tio-star"></i>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4 px-3">
                        <div class="d-flex justify-content-end">
                            {!! $reviews->links() !!}
                        </div>
                    </div>
                    @if(count($reviews)==0)
                        <div class="text-center p-4">
                            <img class="mb-3 width-7rem" src="{{asset('public/assets/admin//svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                            <p class="mb-0">{{ translate('No data to show') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

