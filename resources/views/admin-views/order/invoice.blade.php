<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('Invoice') }}</title>
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/font/open-sans.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/style.css')}}">
</head>

<body class="footer-offset">
<main id="content" role="main" class="main pointer-event">
    <div class="content container-fluid">
        <div class="row">
            @php($logo = Helpers::get_business_settings('logo'))
            <div class="col-12 text-center mb-3">
                <img width="150"
                     src="{{Helpers::onErrorImage(
                            $logo,
                            asset('storage/app/public/ecommerce').'/' . $logo,
                            asset('public/assets/admin/img/160x160/img2.jpg') ,
                            'ecommerce/')}}"
                     alt="{{  translate('logo') }}">
                <h3 class="mb-5 mt-2">{{ translate('Invoice') }} : #{{$order['id']}}</h3>
            </div>
            <div class="col-6 text-dark">
                @if($order->customer)
                    <h3>{{ translate('Customer Info') }}</h3>

                    <div>{{$order->customer['f_name'].' '.$order->customer['l_name']}}</div>
                    <div>{{$order->customer['email']}}</div>
                    <div>{{$order->customer['phone']}}</div>
                    <div>{{$order->delivery_address?$order->delivery_address['address']:''}}</div><br>
                @endif
            </div>

            <div class="col-6 text-dark text-right">
                <h3>{{ translate('Billing Address') }}</h3>
                <div>{{Helpers::get_business_settings('phone')}}</div>
                <div>{{Helpers::get_business_settings('email_address')}}</div>
                <div>{{Helpers::get_business_settings('address')}}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @php($item_amount=0)
                @php($sub_total=0)
                @php($total_tax=0)
                @php($total_dis_on_pro=0)
                @php($total_item_discount=0)

                <div class="table-responsive">
                    <table class="table table-bordered table-align-middle text-dark">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Item Description') }}</th>
                            <th>{{ translate('Unit Price') }}</th>
                            <th>{{ translate('Discount') }}</th>
                            <th>{{ translate('Qty') }}</th>
                            <th class="text-right">{{ translate('Total') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->details as $detail)
                            @if($detail->product_details != null)
                                @php($product = json_decode($detail->product_details, true))

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="media gap-3 max-content">
                                            <div class="avatar-xl">
                                                @if($detail->product && $detail->product['image'] != null )
                                                    <img class="img-fit"
                                                         src="{{$detail->product['image_fullpath'][0]}}"
                                                         alt="{{ translate('image') }}">
                                                @else
                                                    <img src="{{asset('public/assets/admin/img/160x160/img2.jpg')}}"
                                                         class="img-fit img-fluid rounded aspect-ratio-1" alt="{{ translate('image') }}">
                                                @endif
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1 w-24ch">{{$product['name']}}</h6>
                                                @if(count(json_decode($detail['variation'],true))>0)
                                                    <h6 class="underline mb-0">{{ translate('variation') }}:</h6>
                                                    @foreach(json_decode($detail['variation'],true)[0] ?? json_decode($detail['variation'],true) as $key1 =>$variation)
                                                        <div class="fs-12">{{$key1}}: {{$variation}}</div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ Helpers::set_symbol($detail['price']) }}</td>
                                    <td>{{ Helpers::set_symbol($detail['discount_on_product'])}}</td>
                                    <td>{{$detail['quantity']}}</td>
                                    <td class="text-right">
                                        @php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity'])
                                        {{ Helpers::set_symbol($amount) }}
                                    </td>
                                </tr>
                                @php($item_amount+=$detail['price']*$detail['quantity'])
                                @php($sub_total+=$amount)
                                @php($total_tax+=$detail['tax_amount']*$detail['quantity'])
                                @php($total_item_discount += $detail['discount_on_product'] * $detail['quantity'])
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-md-end">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-6">{{ translate('Items Price') }}:</dt>
                            <dd class="col-sm-6 text-right">{{ Helpers::set_symbol($item_amount) }}</dd>

                            <dt class="col-sm-6">{{ translate('item_discount') }}:</dt>
                            <dd class="col-sm-6 text-right">{{ Helpers::set_symbol($total_item_discount) }}</dd>

                            <dt class="col-sm-6">{{ translate('Tax / VAT') }}:</dt>
                            <dd class="col-sm-6 text-right">{{ Helpers::set_symbol($total_tax) }}</dd>

                            <dt class="col-sm-6">{{ translate('Subtotal') }}:</dt>
                            <dd class="col-sm-6 text-right">
                                {{ Helpers::set_symbol($sub_total+$total_tax) }}</dd>

                            <dt class="col-sm-6">{{ translate('Coupon Discount') }}:</dt>
                            <dd class="col-sm-6 text-right">
                                - {{ Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>

                            <dt class="col-6">{{translate('Extra Discount')}}:</dt>
                            <dd class="col-6 text-right">
                                - {{ Helpers::set_symbol($order['extra_discount']) }}</dd>

                            <dt class="col-sm-6">{{ translate('Delivery Fee') }}:</dt>
                            <dd class="col-sm-6 text-right">
                                @if($order['order_type']=='self_pickup')
                                    @php($del_c=0)
                                @else
                                    @php($del_c=$order['delivery_charge'])
                                @endif
                                {{ Helpers::set_symbol($del_c) }}
                            </dd>

                            <dt class="col-sm-6 border-top font-weight-bold pt-2">{{ translate('Total') }}:</dt>
                            <dd class="col-sm-6 border-top font-weight-bold text-right pt-2">{{ Helpers::set_symbol($sub_total+$del_c+$total_tax-$order['coupon_discount_amount']-$order['extra_discount']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-dark mt-10">
            <div class="text-center mb-3">{{ translate('If you require any assistance or have feedback or suggestions about our site you can') }}
                 <br /> {{ translate('email us at') }} <a href="#" class="text-primary">{{ Helpers::get_business_settings('email_address') }}</a>
            </div>

            <div class="invoice-footer-bg py-5 px-4">
                <div class="text-center">
                    <div>{{ translate('phone') }}: {{ Helpers::get_business_settings('phone') }}</div>
                    <div>{{ translate('email') }}: {{ Helpers::get_business_settings('email_address') }}</div>
                    <div><?php echo url('/') ?></div>
                    <div>
                        &copy; {{ Helpers::get_business_settings('restaurant_name') }}.
                        {{ Helpers::get_business_settings('footer_text') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{asset('public/assets/admin/js/demo.js')}}"></script>
<script src="{{asset('public/assets/admin/js/vendor.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/theme.min.js')}}"></script>
<script>
    window.print();
</script>
</body>
</html>
