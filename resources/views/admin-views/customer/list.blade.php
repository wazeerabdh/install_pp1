@extends('layouts.admin.app')

@section('title', translate('Customer List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="{{ translate('customer') }}">
                {{translate('customers')}}
            </h2>
        </div>

        <div class="card">
            <div class="px-20 py-3 d-flex flex-wrap gap-3 justify-content-between">
                <h5 class="d-flex align-items-center gap-2 mb-0">
                    {{translate('customer_List')}}
                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $customers->total() }}</span>
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
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('customer_name')}}</th>
                            <th>{{translate('contact_info')}}</th>
                            <th>{{translate('total_Order')}}</th>
                            <th class="text-center">{{translate('actions')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($customers as $key=>$customer)
                        <tr>
                            <td>
                                {{$customers->firstitem()+$key}}
                            </td>
                            <td>
                                <a class="text-dark media gap-3 align-items-center" href="{{route('admin.customer.view',[$customer['id']])}}">
                                    <div class="avatar rounded-circle">
                                        <img class="img-fit rounded-circle" src="{{$customer['image_fullpath']}}" alt="{{ translate('customer') }}">
                                    </div>
                                    <div class="media-body">{{$customer['f_name']." ".$customer['l_name']}}</div>
                                </a>
                            </td>
                            <td>
                                <div><a class="text-dark" href="mailto:{{$customer['email']}}">{{$customer['email']}}</a></div>
                                <div><a class="text-dark" href="tel:{{$customer['phone']}}">{{$customer['phone']}}</a></div>
                            </td>
                            <td>
                                <label class="badge badge-soft-info">
                                    {{$customer->orders->count()}}
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="btn btn-outline-primary btn-sm square-btn" href="{{route('admin.customer.view',[$customer['id']])}}">
                                        <i class="tio-visible"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $customers->links() !!}
                </div>
            </div>
            @if(count($customers)==0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin//svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection


