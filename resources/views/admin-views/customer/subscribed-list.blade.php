@extends('layouts.admin.app')

@section('title', translate('Subscribed List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="{{ translate('customer') }}">
                {{translate('Subscribed_Customers')}}
            </h2>
        </div>

        <div class="card">
            <div class="px-20 py-3 d-flex flex-wrap gap-3 justify-content-between">
                <h5 class="d-flex align-items-center gap-2 mb-0">
                    {{translate('subscribed_Customers_List')}}
                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $newsletters->total() }}</span>
                </h5>
                <form action="{{url()->current()}}" method="GET">
                    <div class="input-group">
                        <input id="datatableSearch_" type="search" name="search"
                            class="form-control"
                            placeholder="{{translate('Search by Email')}}" aria-label="Search"
                            value="{{ $search }}" required autocomplete="off">
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
                            <th>
                                {{translate('SL')}}
                            </th>
                            <th class="text-center">{{translate('email')}}</th>
                            <th class="text-center">{{translate('subscribed_at')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($newsletters as $key=>$newsletter)
                        <tr>
                            <td>{{$newsletters->firstitem()+$key}}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <a class="text-dark" href="mailto:{{$newsletter['email']}}?subject={{translate('Mail from '). Helpers::get_business_settings('restaurant_name')}}">{{$newsletter['email']}}</a>
                                </div>
                            </td>
                            <td class="text-center">{{date('Y/m/d '.config('timeformat'), strtotime($newsletter->created_at))}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $newsletters->links() !!}
                </div>
            </div>
            @if(count($newsletters)==0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin//svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>

        <div class="modal fade" id="add-point-modal" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="modal-content"></div>
            </div>
        </div>
    </div>
@endsection

