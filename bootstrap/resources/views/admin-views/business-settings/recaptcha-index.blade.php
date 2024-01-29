@extends('layouts.admin.app')

@section('title', translate('reCaptcha Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/third-party.png')}}" alt="{{ translate('3rd_Party_image') }}">
                {{translate('3rd_Party')}}
            </h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial.third-party-nav')
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    @php($config=Helpers::get_business_settings('recaptcha'))
                    <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.recaptcha_update',['recaptcha']):'javascript:'}}" method="post">
                        @csrf
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="text-uppercase mb-0">{{translate('reCaptcha')}}</h5>
                            <label class="switcher">
                                <input class="switcher_input" type="checkbox" name="status" {{isset($config) && $config['status'] == 1? 'checked' : ''}}>
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                        <div class="card-body">
                            <div class="flex-between">
                                <div class="btn-sm btn-dark p-2 cursor-pointer" data-toggle="modal" data-target="#recaptcha-modal">
                                    <i class="tio-info-outlined"></i> {{translate('Credentials SetUp')}}
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="form-group">
                                    <label class="text-capitalize">{{translate('Site Key')}}</label>
                                    <input type="text" class="form-control" name="site_key"
                                           value="{{env('APP_MODE')!='demo'?$config['site_key']??"":''}}">
                                </div>

                                <div class="form-group">
                                    <label class="text-capitalize">{{translate('Secret Key')}}</label>
                                    <input type="text" class="form-control" name="secret_key"
                                           value="{{env('APP_MODE')!='demo'?$config['secret_key']??"":''}}">
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                            class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="recaptcha-modal" data-backdrop="static" data-keyboard="false"
            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog text-dark">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{translate('reCaptcha credential Set up Instructions')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ol>
                        <li>{{translate('Go to the Credentials page')}}
                            ({{translate('Click')}} <a
                                href="https://www.google.com/recaptcha/admin/create"
                                target="_blank">{{translate('here')}}</a>)
                        </li>
                        <li>{{translate('Add a ')}}<b>{{translate('label')}}</b> {{translate('(Ex: Test Label)')}}</li>
                        <li>{{translate('Select reCAPTCHA v2 as ')}}<b>{{translate('reCAPTCHA Type')}}</b>({{translate("Sub type: I'm not a robot Checkbox")}})</li>
                        <li>{{translate('Add')}}<b>{{translate('domain')}}</b>{{translate('(For ex: demo.abc.com)')}}</li>
                        <li>{{translate('Check in ')}}<b>{{translate('Accept the reCAPTCHA Terms of Service')}}</b></li>
                        <li>{{translate('Press')}}<b>{{translate('Submit')}}</b></li>
                        <li>{{translate('Copy')}} <b>{{ translate('Site Key') }}</b> {{translate('and')}} <b>{{ translate('Secret Key') }}</b>, {{translate('paste in the input filed below and')}}<b>{{ translate('Save') }}</b>.</li>
                    </ol>
                    <div class="d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-primary"
                        data-dismiss="modal">{{translate('Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
