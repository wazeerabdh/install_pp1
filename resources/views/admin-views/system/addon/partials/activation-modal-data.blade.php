<div class="modal-header border-0 pb-0 d-flex justify-content-end">
    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close">
        <i class="tio-clear"></i>
    </button>
</div>
<div class="modal-body px-4 px-sm-5">
    <div class="mb-4 text-center">
        @php($logo= Helpers::get_business_settings('logo'))
        <img width="200" src="{{Helpers::onErrorImage(
            $logo,
            asset('storage/app/public/ecommerce') . '/' . $logo,
            asset('public/assets/admin/img/160x160/img2.jpg'),
            'ecommerce/')}}"
            alt="{{ translate('logo') }}" class="dark-support">
    </div>
    <h2 class="text-center mb-4">{{$addonName}}</h2>

    <form action="{{route('admin.system-addon.activation')}}" method="post" id="customer_login_modal" autocomplete="off">
        @csrf
        <div class="form-group mb-4">
            <label for="username">{{ translate('Codecanyon') }} {{ translate('username') }}</label>
            <input name="username" id="username" class="form-control" placeholder="{{translate('Ex:john')}}" required>
        </div>
        <div class="form-group mb-6">
            <label for="purchase_code">{{ translate('Purchase') }} {{ translate('Code') }}</label>
            <input name="purchase_code" id="purchase_code" class="form-control" placeholder="{{translate('Ex: 987652')}}" required>
            <input type="text" name="path" class="form-control" value="{{$path}}" hidden>
        </div>

        <div class="d-flex justify-content-center gap-3 mb-3">
            <button type="button" class="fs-16 btn btn-secondary flex-grow-1" data-dismiss="modal">{{ translate('cancel') }}</button>
            <button type="submit" class="fs-16 btn btn-primary flex-grow-1">{{ translate('Activate') }}</button>
        </div>
    </form>
</div>
