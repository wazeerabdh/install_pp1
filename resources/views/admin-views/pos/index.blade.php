@extends('layouts.admin.app')
@section('title', translate('POS'))
@section('content')
    <div class="content container-fluid">
        <div class="row gy-3 gx-2">
            <div class="col-lg-7">
                <div class="card overflow-hidden">
                    <div class="pos-title">
                        <h4 class="mb-0">{{translate('Product_Section')}}</h4>
                    </div>

                    <div class="d-flex flex-wrap flex-md-nowrap justify-content-between gap-3 gap-xl-4 px-4 py-4">
                        <div class="w-100 mr-xl-2">
                            <select name="category" id="category" class="form-control js-select2-custom mx-1"
                                    title="{{translate('select category')}}" onchange="set_category_filter(this.value)">
                                <option value="">{{translate('All Categories')}}</option>
                                @foreach ($categories as $item)
                                    <option value="{{$item->id}}" {{$category==$item->id?'selected':''}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-100 mr-xl-2">
                            <form id="search-form" class="header-item">
                                <div class="input-group input-group-merge input-group-flush border rounded">
                                    <div class="input-group-prepend pl-2">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch" type="search" value="{{$keyword?$keyword:''}}" name="search"
                                        class="form-control border-0 pr-2"
                                        placeholder="{{translate('Search here')}}"
                                        aria-label="Search here">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body pt-0" id="items">
                        <div class="pos-item-wrap justify-content-center">
                            @foreach($products as $product)
                                @include('admin-views.pos._single_product',['product'=>$product])
                            @endforeach
                        </div>
                    </div>
                    <div class="px-3 d-flex justify-content-end">
                        {!!$products->withQueryString()->links()!!}
                    </div>
                    @if(count($products)==0)
                        <div class="text-center p-4">
                            <img class="mb-3 width-7rem" src="{{asset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="{{ translate('image') }}">
                            <p class="mb-0">{{ translate('No data to show') }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card billing-section-wrap">
                    <div class="pos-title">
                        <h4 class="mb-0">{{translate('Billing_Section')}}</h4>
                    </div>
                    <div class="p-2 p-sm-4">
                        <div class="form-group d-flex gap-2">
                            <select id='customer' name="customer_id" onchange="store_key('customer_id',this.value)"
                                    data-placeholder="{{translate('Walk In Customer')}}"
                                    class="js-data-example-ajax form-control m-1">
                                <option  value="" selected disabled>{{translate('Walking Customer')}}</option>
                                @foreach($users as $user)
                                    <option value="{{$user['id']}}" {{ session()->get('customer_id') == $user['id'] ? 'selected' : '' }}>{{$user['f_name']. ' '. $user['l_name'] }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-success rounded text-nowrap" id="add_new_customer" type="button" data-toggle="modal" data-target="#add-customer" title="Add Customer">
                                <i class="tio-add"></i>
                                {{translate('Customer')}}
                            </button>
                        </div>

                        <div class="form-group d-flex">
                            <select onchange="store_key('branch_id',this.value)" id='branch' name="branch_id" class="js-data-example-ajax-2 form-control js-select2-custom">
                                @foreach($branches as $branch)
                                    <option value="{{$branch['id']}}" {{ session()->get('branch_id') == $branch['id'] ? 'selected' : '' }}>{{$branch['name']}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="cart">
                            @include('admin-views.pos._cart')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quick-view" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="quick-view-modal">

            </div>
        </div>
    </div>

    <div class="modal fade" id="add-customer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{translate('Add_New_Customer')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.pos.customer-store')}}" method="post" id="customer-form">
                        @csrf
                        <div class="row pl-2">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label">{{translate('First_Name')}}<span class="input-label-secondary text-danger">*</span></label>
                                    <input type="text" name="f_name" class="form-control" value="" placeholder="{{ translate('First name') }}" required="">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label">{{translate('Last_Name')}}<span class="input-label-secondary text-danger">*</span></label>
                                    <input type="text" name="l_name" class="form-control" value="" placeholder="{{ translate('Last name') }}" required="">
                                </div>
                            </div>
                        </div>
                        <div class="row pl-2">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label">{{translate('Email')}}<span class="input-label-secondary text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="" placeholder="{{ translate('Ex : ex@example.com') }}" required="">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label">{{translate('Phone')}}<span class="input-label-secondary text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="" placeholder="{{ translate('Phone') }}" required="">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" id="" class="btn btn-primary">{{translate('Submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php($order=\App\Model\Order::find(session('last_order')))
    @if($order)
        @php(session(['last_order'=> false]))
        <div class="modal fade" id="print-invoice" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('Print Invoice')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body row front-emoji">
                        <div class="col-md-12 text-center">
                            <div>
                                <input type="button" class="btn btn-primary non-printable" id="print-invoice-div"
                                       data-divName="printableArea"
                                    value="{{translate('Proceed, If thermal printer is ready.')}}"/>
                                <a href="{{url()->previous()}}"
                                class="btn btn-danger non-printable">{{translate('Back')}}</a>
                            </div>
                            <hr class="non-printable">
                        </div>
                        <div class="row m-auto" id="printableArea">
                            @include('admin-views.pos.order.invoice')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).on('ready', function () {
            @if($order)
                $('#print-invoice').modal('show');
            @endif
        });

        $("#print-invoice-div").on('click', function (){
            let name = $(this).data('divName');
            printDiv(name);
        });

        function printDiv(divName) {
            let printContents = document.getElementById(divName).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function set_category_filter(id) {
            let nurl = new URL('{!!url()->full()!!}');
            nurl.searchParams.set('category_id', id);
            location.href = nurl;
        }

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let keyword = $('#datatableSearch').val();
            let nurl = new URL('{!!url()->full()!!}');
            nurl.searchParams.set('keyword', keyword);
            location.href = nurl;
        });

        $('.pos-single-product-card').on('click', function (){
            let productId = $(this).data('id');
            quickView(productId);
        });

        function quickView(product_id) {
            $.ajax({
                url: '{{route('admin.pos.quick-view')}}',
                type: 'GET',
                data: {
                    product_id: product_id
                },
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    console.log("success...");
                    console.log(data);

                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }

        function checkAddToCartValidity() {
            return true;
        }

        function cartQuantityInitialize() {
            $('.btn-number').click(function (e) {
                e.preventDefault();

                let fieldName = $(this).attr('data-field');
                let type = $(this).attr('data-type');
                let input = $("input[name='" + fieldName + "']");
                let currentVal = parseInt(input.val());

                if (!isNaN(currentVal)) {
                    if (type == 'minus') {

                        if (currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('min')) {
                            $(this).attr('disabled', true);
                        }

                    } else if (type == 'plus') {
                        if (currentVal <  parseInt(input.attr('max')) ) {
                            input.val(currentVal + 1).change();
                        }
                        if (currentVal >=  parseInt(input.attr('max')) ) {
                            console.log(input.val(currentVal))
                            Swal.fire({
                                icon: 'error',
                                title: '{{translate('Cart')}}',
                                confirmButtonText:'{{translate("Ok")}}',
                                text: '{{translate('stock limit exceeded')}}.'
                            });
                            input.val(currentVal).change();
                        }
                    }
                } else {
                    input.val(0);
                }
            });

            $('.input-number').focusin(function () {
                $(this).data('oldValue', $(this).val());
            });

            $('.input-number').change(function () {

                let minValue = parseInt($(this).attr('min'));
                let maxValue = parseInt($(this).attr('max'));
                let valueCurrent = parseInt($(this).val());

                let name = $(this).attr('name');
                if (valueCurrent >= minValue) {
                    $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{translate('Cart')}}',
                        confirmButtonText:'{{translate("Ok")}}',
                        text: '{{translate('Sorry, the minimum value was reached')}}'
                    });
                    $(this).val($(this).data('oldValue'));
                }
                if (valueCurrent <= maxValue) {
                    $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{translate('Cart')}}',
                        confirmButtonText:'{{translate("Ok")}}',
                        text: '{{translate('Sorry, stock limit exceeded')}}.'
                    });
                    $(this).val($(this).data('oldValue'));
                }
            });
            $(".input-number").keydown(function (e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        function getVariantPrice() {
            if ($('#add-to-cart-form input[name=quantity]').val() > 0 && checkAddToCartValidity()) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '{{ route('admin.pos.variant_price') }}',
                    data: $('#add-to-cart-form').serializeArray(),
                    success: function (data) {
                        $('#add-to-cart-form #chosen_price_div').removeClass('d-none');
                        $('#add-to-cart-form #chosen_price_div #chosen_price').html(data.price);
                        $('#add-to-cart-form #quantity_div #quantity').attr({"max" : data.stock});
                    }
                });
            }
        }

        $('.add-to-shopping-cart').on('click', function (){
            addToCart();
        });

        function addToCart(form_id = 'add-to-cart-form') {
            if (checkAddToCartValidity()) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.post({
                    url: '{{ route('admin.pos.add-to-cart') }}',
                    data: $('#' + form_id).serializeArray(),
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
                        console.log(data)
                        if (data.data == 1) {
                            Swal.fire({
                                icon: 'info',
                                title: '{{translate('Cart')}}',
                                confirmButtonText:'{{translate("Ok")}}',
                                text: "{{translate('Product already added in cart')}}"
                            });
                            return false;
                        } else if (data.data == 0) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{translate('Cart')}}',
                                confirmButtonText:'{{translate("Ok")}}',
                                text: '{{translate('Sorry, product out of stock')}}.'
                            });
                            return false;
                        }
                        $('.call-when-done').click();

                        toastr.success('{{translate('Item has been added in your cart')}}!', {
                            CloseButton: true,
                            ProgressBar: true
                        });

                        updateCart();
                    },
                    complete: function () {
                        $('#loading').hide();
                    }
                });
            } else {
                Swal.fire({
                    type: 'info',
                    title: '{{translate('Cart')}}',
                    confirmButtonText:'{{translate("Ok")}}',
                    text: '{{translate('Please choose all the options')}}'
                });
            }
        }

        function removeFromCart(key) {
            $.post('{{ route('admin.pos.remove-from-cart') }}', {_token: '{{ csrf_token() }}', key: key}, function (data) {
                if (data.errors) {
                    for (let i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    updateCart();
                    toastr.info('{{translate('Item has been removed from cart')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

            });
        }

        $('.pos-empty-cart').on('click', function (){
            emptyCart();
        });

        function emptyCart() {
            $.post('{{ route('admin.pos.emptyCart') }}', {_token: '{{ csrf_token() }}'}, function (data) {
                updateCart();
                toastr.info('{{translate('Item has been removed from cart')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                location.reload();
            });
        }

        function updateCart() {
            $.post('<?php echo e(route('admin.pos.cart_items')); ?>', {_token: '<?php echo e(csrf_token()); ?>'}, function (data) {
                $('#cart').empty().html(data);
            });
        }

        function store_key(key, value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}"
                }
            });
            $.post({
                url: '{{route('admin.pos.store-keys')}}',
                data: {
                    key:key,
                    value:value,
                },
                success: function (data) {
                    let selected_field_text = key;
                    var selected_field = selected_field_text.replace("_", " ");
                    var selected_field = selected_field.replace("id", " ");
                    var message = selected_field+' '+'selected!';
                    var new_message = message.charAt(0).toUpperCase() + message.slice(1);
                    toastr.success((new_message), {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
            });
        }

        $(function () {
            $(document).on('click', 'input[type=number]', function () {
                this.select();
            });
        });

        function updateQuantity(e) {
            let element = $(e.target);
            let minValue = parseInt(element.attr('min'));
            // maxValue = parseInt(element.attr('max'));
            let valueCurrent = parseInt(element.val());

            let key = element.data('key');
            if (valueCurrent >= minValue) {
                $.post('{{ route('admin.pos.updateQuantity') }}', {
                    _token: '{{ csrf_token() }}',
                    key: key,
                    quantity: valueCurrent
                }, function (data) {
                    updateCart();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '{{translate('Cart')}}',
                    confirmButtonText:'{{translate("Ok")}}',
                    text: '{{translate('Sorry, the minimum value was reached')}}'
                });
                element.val(element.data('oldValue'));
            }


            if (e.type == 'keydown') {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            }

        }

        $('.js-select2-custom').each(function () {
            let select2 = $.HSCore.components.HSSelect2.init($(this));
        });

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{route('admin.pos.customers')}}',
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });


        $('.js-data-example-ajax-2').select2()

        $('#order_place').submit(function (eventObj) {
            if ($('#customer').val()) {
                $(this).append('<input type="hidden" name="user_id" value="' + $('#customer').val() + '" /> ');
            }
            return true;
        });

    </script>
    <script>
        if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
    </script>
@endpush
