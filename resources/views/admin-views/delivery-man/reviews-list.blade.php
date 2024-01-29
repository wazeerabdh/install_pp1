@extends('layouts.admin.app')

@section('title', translate('Review List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/rating.png')}}" alt="{{ translate('rating') }}">
                {{translate('review_List')}}
            </h2>
        </div>

        <div class="card">
            <div class="px-20 py-3 d-flex flex-wrap gap-3 justify-content-between">
                <h5 class="d-flex align-items-center gap-2 mb-0">
                    {{translate('Delivery Men Review Table')}}
                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $reviews->total() }}</span>
                </h5>
                <form action="{{url()->current()}}" method="GET">
                    <div class="input-group">
                        <input id="datatableSearch_" type="search" name="search"
                            class="form-control"
                            placeholder="{{translate('Search by Name')}}" aria-label="Search"
                            value="{{$search}}" required autocomplete="off">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">{{translate('search')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('deliveryman')}}</th>
                            <th>{{translate('customer')}}</th>
                            <th>{{translate('review')}}</th>
                            <th>{{translate('rating')}}</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($reviews as $key=>$review)
                            <tr>
                                <td>{{$reviews->firstitem()+$key}}</td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                            @if($review->delivery_man)
                                                <a class="text-dark" href="{{route('admin.delivery-man.preview',[$review['delivery_man_id']])}}">
                                                    {{$review->delivery_man->f_name.' '.$review->delivery_man->l_name}}
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    {{translate('DeliveryMan Unavailable')}}
                                                </span>
                                            @endif
                                    </span>
                                </td>
                                <td>
                                    @if(isset($review->customer))
                                        <a class="text-dark" href="{{route('admin.customer.view',[$review->user_id])}}">
                                            {{$review->customer->f_name." ".$review->customer->l_name}}
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            {{translate('Customer unavailable')}}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="mx-w300 mn-w200 text-wrap">
                                        {{$review->comment}}
                                    </div>
                                </td>
                                <td>
                                    <label class="badge badge-soft-info">
                                        {{$review->rating}} <i class="tio-star ml-1"></i>
                                    </label>
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
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

