@extends('layouts.blank')

@section('content')
    <div class="text-center text-white mb-4">
        <h2>{{ translate('Hexacom Software Installation') }}</h2>
        <h6 class="fw-normal">{{ translate('Please proceed step by step with proper data according to instructions') }}</h6>
    </div>

    <div class="pb-2">
        <div class="progress cursor-pointer" role="progressbar" aria-label="Grofresh Software Installation"
             aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip"
             data-bs-placement="top" data-bs-custom-class="custom-progress-tooltip" data-bs-title="First Step!"
             data-bs-delay='{"hide":1000}'>
            <div class="progress-bar w-20-percent"></div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="p-4 mb-md-3 mx-xl-4 px-md-5">
            <div class="d-flex justify-content-end mb-2">
                <a href="https://docs.6amtech.com/docs-hexacom/intro" class="d-flex align-items-center gap-1"
                   target="_blank">
                    {{ translate('Read Documentation') }}
                    <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                          data-bs-title="Follow our documentation">
                        <img src="{{asset('public/assets/installation/assets/img/svg-icons/info.svg')}}" class="svg" alt="{{ translate('image') }}">
                    </span>
                </a>
            </div>

            <div class="d-flex align-items-center column-gap-3 flex-wrap mb-4">
                <h5 class="fw-bold fs text-uppercase">{{ translate('Step 1') }}. </h5>
                <h5 class="fw-normal">{{ translate('Check & Verify File Permissions') }}</h5>
            </div>

            <div class="bg-light p-4 rounded mb-4">
                <h6 class="fw-bold text-uppercase fs m-0 letter-spacing  mb-4 pb-sm-3 fs-14px">
                   {{ translate(' Required Database Information') }}
                </h6>

                <div class="px-xl-2 pb-sm-3">
                    <div class="row g-4 g-md-5">
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/php-version.svg')}}" alt="{{ translate('image') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ translate('PHP Version 8.0 +') }}

                                    @php($phpVersion = number_format((float)phpversion(), 2, '.', ''))
                                    @if ($phpVersion >= 8.0)
                                        <img width="20" src="{{asset('public/assets/installation/assets/img/svg-icons/check.png')}}" alt="{{ translate('image') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Your php version in server is lower than 8.0 version
                                                   <a href='https://support.cpanel.net/hc/en-us/articles/360052624713-How-to-change-the-PHP-version-for-a-domain-in-cPanel-or-WHM'
                                                   class='d-block' target='_blank'>See how to update</a> ">
                                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/info.svg')}}"
                                                    class="svg text-danger" alt="{{ translate('image') }}">
                                            </span>
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/curl-enabled.svg')}}" alt="{{ translate('image') }}">
                                <div
                                    class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ translate('Curl Enabled') }}

                                    @if ($permission['curl_enabled'])
                                        <img width="20" src="{{asset('public/assets/installation/assets/img/svg-icons/check.png')}}" alt="{{ translate('image') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Curl extension is not enabled in your server. To enable go to PHP version > extensions and select curl.">
                                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/info.svg')}}"
                                                    class="svg text-danger" alt="{{ translate('image') }}">
                                            </span>
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/route-service.svg')}}" alt="{{ translate('image') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ translate('.env File Permission') }}

                                    @if ($permission['db_file_write_perm'])
                                        <img width="20" src="{{asset('public/assets/installation/assets/img/svg-icons/check.png')}}" alt="{{ translate('image') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Please change env permission">
                                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/info.svg')}}"
                                                    class="svg text-danger" alt="{{ translate('image') }}">
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/route-service.svg')}}" alt="{{ translate('image') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ translate('RouteServiceProvider.php File Permission') }}

                                    @if ($permission['routes_file_write_perm'])
                                        <img width="20" src="{{asset('public/assets/installation/assets/img/svg-icons/check.png')}}" alt="{{ translate('image') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Please change RouteServiceProvider permission">
                                                <img src="{{asset('public/assets/installation/assets/img/svg-icons/info.svg')}}" class="svg text-danger" alt="{{ translate('image') }}">
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p>{{ translate('All the permissions are provided successfully') }} ? </p>

                @if ($permission['curl_enabled'] == 1 && $permission['db_file_write_perm'] == 1 && $permission['routes_file_write_perm'] == 1 && $phpVersion >= 8.0)
                    <a href="{{ route('step2',['token'=>bcrypt('step_2')]) }}" class="btn btn-dark px-sm-5">{{ translate('Proceed Next') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection

