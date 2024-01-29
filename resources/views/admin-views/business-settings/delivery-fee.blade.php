@extends('layouts.admin.app')

@section('title', translate('delivery_fee'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/business-setup.png')}}" alt="{{ translate('business_setup') }}">
                {{translate('business_Setup')}}
            </h2>
        </div>

        <div class="inline-page-menu mb-4">
            @include('admin-views.business-settings.partial.business-setup-nav')
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.business-settings.update-delivery-fee')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            @php($config=Helpers::get_business_settings('delivery_management'))
                            <div class="d-flex gap-2 align-items-center my-2">
                                <input type="radio" name="shipping_status" value="1"
                                       {{$config['status']==1?'checked':''}} id="shipping_by_distance_status">
                                <label for="shipping_by_distance_status" class="text-dark font-weight-bold mb-0">{{translate('delivery_charge_by_distance')}}</label>
                            </div>

                            <div class="form-group">
                                <div class="form-group pl-3">
                                    <label>{{translate('Minimum delivery Charge')}} </label><br>
                                    <input type="number" step=".01" class="form-control"
                                           name="min_shipping_charge"
                                           value="{{$config['min_shipping_charge']}}"
                                           id="min_shipping_charge" {{ $config['status']==0?'disabled':'' }} >
                                </div>
                                <div class="form-group pl-3">
                                    <label>{{translate('delivery Charge / Kilometer')}}</label><br>
                                    <input type="number" step=".01" class="form-control" name="shipping_per_km"
                                           value="{{$config['shipping_per_km']}}"
                                           id="shipping_per_km" {{ $config['status']==0?'disabled':'' }}>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-6 col-sm-12">
                            @php($config=Helpers::get_business_settings('delivery_management'))

                            <div class="form-group">
                                <div class="d-flex gap-2 align-items-center my-2">
                                    <input type="radio" name="shipping_status" value="0"
                                           {{$config['status']==0?'checked':''}} id="default_delivery_status">
                                    <label for="default_delivery_status" class="text-dark font-weight-bold mb-0">{{translate('default_delivery_charge')}}</label>
                                </div>
                                <div class="form-group pl-3">
                                    @php($delivery=\App\Model\BusinessSetting::where('key','delivery_charge')->first()->value)
                                    <div class="form-group pl-3">
                                        <label for="exampleFormControlInput1">{{translate('Delivery Charge')}} </label>
                                        <input type="number" min="0" step=".01" name="delivery_charge" value="{{$delivery}}"
                                               class="form-control" placeholder="{{ translate('EX: 100') }}" required
                                               {{ $config['status']==1?'disabled':'' }} id="delivery_charge">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-5">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('submit')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/business-settings.js') }}"></script>
@endpush
