@extends('layouts.admin.app')

@section('title', translate('business_setup'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/business-setup.png')}}" alt="{{ translate('business-setup') }}">
                {{translate('business_Setup')}}
            </h2>
        </div>

        <div class="inline-page-menu mb-4">
            @include('admin-views.business-settings.partial.business-setup-nav')
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="border gap-2 rounded border-primary px-4 py-3 d-flex justify-content-between">
                    @php($config=Helpers::get_business_settings('maintenance_mode'))
                    <h5 class="mb-0 d-flex text-primary">
                        {{translate('Maintenance_Mode')}}
                    </h5>
                    <label class="switcher">
                        <input type="checkbox" class="switcher_input" id="maintenance_mode"
                            {{isset($config) && $config?'checked':''}}>
                        <span class="switcher_control"></span>
                    </label>
                </div>
                <p class="mb-0 mt-2">{{translate('* By turning on maintenance mode all your app and customer side website will be off. Only admin panel and seller panel will be functional')}}*</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="d-flex align-items-center gap-2 mb-0">
                    <i class="tio-settings"></i>
                    {{ translate('General settings form') }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.update-setup')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        @php($name=Helpers::get_business_settings('restaurant_name'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('Shop Name')}}</label>
                                <input type="text" name="restaurant_name" value="{{$name}}" class="form-control"
                                       placeholder="{{ translate('ABC Company') }}" required>
                            </div>
                        </div>
                        @php($currency_code=Helpers::get_business_settings('currency'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('currency')}}</label>
                                <select name="currency" class="form-control js-select2-custom">
                                    @foreach(\App\Model\Currency::orderBy('currency_code')->get() as $currency)
                                        <option value="{{$currency['currency_code']}}" {{$currency_code==$currency['currency_code']?'selected':''}}>
                                            {{$currency['currency_code']}} ( {{$currency['currency_symbol']}} )
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @php($phone=Helpers::get_business_settings('phone'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('phone')}}</label>
                                <input type="text" value="{{$phone}}" name="phone" class="form-control"
                                       placeholder="" required>
                            </div>
                        </div>
                        @php($email=Helpers::get_business_settings('email_address'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('email')}}</label>
                                <input type="email" value="{{$email}}"
                                       name="email" class="form-control" placeholder="" required>
                            </div>
                        </div>
                        @php($address=Helpers::get_business_settings('address'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{translate('address')}}</label>
                                <input type="text" value="{{$address}}"
                                       name="address" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>

                        @php($pagination_limit=Helpers::get_business_settings('pagination_limit'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{translate('pagination')}} {{translate('settings')}}</label>
                                <input type="text" value="{{$pagination_limit}}"
                                       name="pagination_limit" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        @php($mov=Helpers::get_business_settings('minimum_order_value'))
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{translate('min')}} {{translate('order')}} {{translate('value')}}
                                    ( {{Helpers::currency_symbol()}} )</label>
                                <input type="number" min="1" value="{{$mov}}"
                                       name="minimum_order_value" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        @php($country_code=Helpers::get_business_settings('country')??'BD')
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label" for="country">{{translate('country')}}</label>
                                <select id="country" name="country" class="form-control  js-select2-custom">
                                    @foreach(COUNTRY_CODE as $country)
                                        <option value="{{ $country['code'] }}" {{ $country['code'] == $country_code ? 'selected' : '' }}>{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            @php($sp=Helpers::get_business_settings('self_pickup'))
                            <div class="form-group">
                                <label>{{translate('self_pickup')}}</label>
                                <small class="text-danger"> *</small>
                                <div class="input-group input-group-md-down-break">
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1"
                                                   name="self_pickup"
                                                   id="sp1" {{$sp==1?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="sp1">{{translate('on')}}</label>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0"
                                                   name="self_pickup"
                                                   id="sp2" {{$sp==0?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="sp2">{{translate('off')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            @php($ev=Helpers::get_business_settings('email_verification'))
                            <div class="form-group">
                                <label>{{translate('email')}} {{translate('verification')}} ( Token )</label>
                                <small class="text-danger"> *</small>
                                <div class="input-group input-group-md-down-break">
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1"
                                                   name="email_verification"
                                                   id="email_verification_on" {{$ev==1?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="email_verification_on">{{translate('on')}}</label>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0"
                                                   name="email_verification"
                                                   id="email_verification_off" {{$ev==0?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="email_verification_off">{{translate('off')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            @php($pv=Helpers::get_business_settings('phone_verification'))
                            <div class="form-group">
                                <label>{{translate('phone')}} {{translate('verification')}} ( OTP
                                    )</label><small class="text-danger"> *</small>
                                <div class="input-group input-group-md-down-break">
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1"
                                                   name="phone_verification"
                                                   id="phone_verification_on" {{(isset($pv) && $pv==1)?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="phone_verification_on">{{translate('on')}}</label>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0"
                                                   name="phone_verification"
                                                   id="phone_verification_off" {{(isset($pv) && $pv==0)?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="phone_verification_off">{{translate('off')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            @php($current_time_zone=Helpers::get_business_settings('time_zone'))
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('time_zone')}}</label>
                                <select name="time_zone" id="time_zone" data-maximum-selection-length="3" class="form-control js-select2-custom">
                                    @foreach(TIME_ZONE as $time_zone)
                                        <option value="{{ $time_zone['key'] }}" {{ $time_zone['key'] == $current_time_zone ? 'selected' : '' }}>{{ $time_zone['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @php($footer_text=Helpers::get_business_settings('footer_text'))
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('footer')}} {{translate('text')}}</label>
                                <input type="text" value="{{$footer_text}}"
                                       name="footer_text" class="form-control" placeholder="" required>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-6">
                            @php($config=Helpers::get_business_settings('currency_symbol_position'))
                            <div class="form-group">
                                <label class="d-flex justify-content-between align-items-center"> {{ translate('Currency Symbol Position') }}</i> </label>

                                <div class="input-group input-group-md-down-break">
                                    <div class="form-control">
                                        <div class="custom-control custom-radio custom-radio-reverse">
                                            <input type="radio" class="custom-control-input currency-symbol-position"
                                                   name="projectViewNewProjectTypeRadio"
                                                   data-route="{{ route('admin.business-settings.currency-position',['left']) }}"
                                                   id="projectViewNewProjectTypeRadio1" {{(isset($config) && $config=='left')?'checked':''}}>
                                            <label class="custom-control-label media align-items-center" for="projectViewNewProjectTypeRadio1">
                                                <i class="tio-agenda-view-outlined text-muted mr-2"></i>
                                                <span class="media-body">{{Helpers::currency_symbol()}} {{ translate('Left') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <div class="custom-control custom-radio custom-radio-reverse">
                                            <input type="radio" class="custom-control-input currency-symbol-position"
                                                   name="projectViewNewProjectTypeRadio"
                                                   data-route="{{ route('admin.business-settings.currency-position',['right']) }}"
                                                   id="projectViewNewProjectTypeRadio2" {{(isset($config) && $config=='right')?'checked':''}}>
                                            <label class="custom-control-label media align-items-center"
                                                   for="projectViewNewProjectTypeRadio2">
                                                <i class="tio-table text-muted mr-2"></i>
                                                <span class="media-body">
                                                    {{ translate('Right') }} {{Helpers::currency_symbol()}}
                                                    </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            @php($dm_status=Helpers::get_business_settings('dm_self_registration'))
                            <div class="form-group">
                                <label>{{translate('Deliverman_self_registration')}}
                                    <i class="tio-info-outlined"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="{{ translate('When this field is active, delivery men can register themself using the delivery man app.') }}">
                                    </i>
                                </label>
                                <div class="input-group input-group-md-down-break">
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1"
                                                   name="dm_self_registration"
                                                   id="dm_self_registration_on" {{$dm_status==1?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="dm_self_registration_on">{{translate('on')}}</label>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0"
                                                   name="dm_self_registration"
                                                   id="dm_self_registration_off" {{$dm_status==0?'checked':''}}>
                                            <label class="custom-control-label"
                                                   for="dm_self_registration_off">{{translate('off')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{translate('product')}} {{translate('and')}} {{translate('category')}} {{translate('translation')}}</label>
                                <select name="language[]" id="language" data-maximum-selection-length="3"
                                        class="form-control js-select2-custom" required multiple=true>
                                    @foreach(LANGUAGE_CODE as $language)
                                        <option value="{{ $language['code'] }}">{{ $language['name'] }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label>{{translate('Admin Logo')}}</label>
                                <small class="text-danger"> * ( {{translate('ratio')}} 3:1 )</small>
                                <div class="custom-file">
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                </div>

                                <div class="text-center mt-4">
                                    <img class="upload-img-view h-auto max-w-200" id="viewer"
                                        src="{{ $logo }}" alt="{{ translate('logo_image') }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label>{{translate('Web App Logo')}}</label>
                                <small class="text-danger"> * ( {{translate('ratio')}} 1:1 )</small>
                                <div class="custom-file">
                                    <input type="file" name="app_logo" id="customFileEg3" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                </div>

                                <div class="text-center mt-4">
                                    <img class="upload-img-view h-auto max-w-200" id="viewer_3"
                                        src="{{ $app_logo }}" alt="{{ translate('app_logo_image') }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label>{{translate('fav_icon')}}</label>
                                <small class="text-danger"> * ( {{translate('ratio')}} 1:1 )</small>
                                <div class="custom-file">
                                    <input type="file" name="fav_icon" id="customFileEg2" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="customFileEg2">{{translate('choose')}} {{translate('file')}}</label>
                                </div>

                                <div class="text-center mt-4">
                                    <img class="upload-img-view h-auto max-w-145" id="viewer_2"
                                        src="{{ $fav_icon}}" alt="{{ translate('fav_icon_image') }}"/>
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

    <script>
        "use strict";

        @php($language=\App\Model\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        let language = <?php echo($language); ?>;
        $('[id=language]').val(language);

        $('#maintenance_mode').on('click', function (){
            @if(env('APP_MODE')=='demo'){
                toastr.info('Disabled for demo version!')
            }@else{
                Swal.fire({
                    title: '{{translate("Are you sure?")}}',
                    text:  '{{translate("Be careful before you turn on/off maintenance mode")}}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#377dff',
                    cancelButtonText: '{{translate("No")}}',
                    confirmButtonText: '{{translate("Yes")}}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.get({
                            url: '{{route('admin.business-settings.maintenance-mode')}}',
                            contentType: false,
                            processData: false,
                            beforeSend: function () {
                                $('#loading').show();
                            },
                            success: function (data) {
                                toastr.success(data.message);
                            },
                            complete: function () {
                                $('#loading').hide();
                            },
                        });
                    } else {
                        location.reload();
                    }
                })
            }
            @endif
        })
    </script>
@endpush
