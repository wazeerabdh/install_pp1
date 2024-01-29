@extends('layouts.admin.app')

@section('title', translate('branch_list'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img width="20" src="{{asset('public/assets/admin/img/icons/branch.png')}}" alt="{{ translate('branch') }}">
                {{translate('branch_list')}}
            </h2>
        </div>

        <div class="card">
            <div class="px-20 py-3">
                <div class="row gy-2 align-items-center">
                    <div class="col-sm-4">
                        <h5 class="text-capitalize d-flex align-items-center gap-2 mb-0">
                            {{translate('branch_table')}}
                            <span class="badge badge-soft-dark rounded-50 fz-12">{{ $branches->total() }}</span>
                        </h5>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex flex-wrap justify-content-sm-end gap-2">
                            <form action="#" method="GET">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="search"
                                        class="form-control"
                                        placeholder="{{translate('Search by branch Name')}}" aria-label="Search"
                                        value="" required autocomplete="off">
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
                            <th>{{translate('branch_name')}}</th>
                            <th>{{translate('branch_type')}}</th>
                            <th>{{translate('Contact_info')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($branches as $key=>$branch)
                        <tr>
                            <td>{{$branches->firstItem()+$key}}</td>
                            <td>
                                <div class="media gap-3 align-items-center">
                                    <div class="avatar">
                                        <img class="img-fit"
                                            src="{{$branch['image_fullpath']}}" alt="{{ translate('branch') }}">
                                    </div>
                                    <div class="media-body">
                                        {{$branch['name']}}
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($branch['id']==1)
                                    <div class="text-capitalize">{{translate('main_branch')}}</div>
                                @else
                                    <div class="text-capitalize">{{translate('sub_branch')}}</div>
                                @endif
                            </td>
                            <td>
                                <a class="text-dark" href="mailto:{{$branch['email']}}?subject={{translate('Mail from '). \App\Model\BusinessSetting::where(['key' => 'restaurant_name'])->first()->value}}">{{$branch['email']}}</a>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="btn btn-outline-info square-btn"
                                        href="{{route('admin.branch.edit',[$branch['id']])}}"><i class="tio tio-edit"></i></a>
                                    @if($branch['id']!=1)
                                        <a class="btn btn-outline-danger square-btn form-alert" href="javascript:"
                                           data-id="branch-{{$branch['id']}}"
                                           data-message="{{translate('Want to delete this branch ?')}}">
                                            <i class="tio tio-delete"></i>
                                        </a>
                                    @endif
                                </div>
                                <form action="{{route('admin.branch.delete',[$branch['id']])}}"
                                        method="post" id="branch-{{$branch['id']}}">
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
                    {!! $branches->links() !!}
                </div>
            </div>
            @if(count($branches)==0)
                <div class="text-center p-4">
                    <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                    <p class="mb-0">{{ translate('No data to show') }}</p>
                </div>
            @endif
        </div>
    </div>

@endsection

