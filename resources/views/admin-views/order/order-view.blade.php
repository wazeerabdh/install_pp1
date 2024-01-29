@extends('layouts.admin.app')

@section('title', translate('Order Details'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="text-capitalize mb-0 d-flex align-items-center gap-2">
                <img src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt="{{ translate('order_details') }}">
                {{translate('order_details')}}
                <span class="badge badge-soft-dark rounded-50 fz-14">{{$order->details->count()}}</span>
            </h2>
        </div>
        <div class="row" id="printableArea">
            <div class="col-lg-{{$order->user_id == null ? 12 : 8}} mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-body">
                        <div class="mb-3 text-dark d-print-none">
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column justify-content-between h-100">
                                        <div class="d-flex flex-column gap-2">
                                            <h2 class="page-header-title">{{translate('order')}} #{{$order['id']}}</h2>
                                            <div>
                                                <i class="tio-date-range"></i> {{date('d M Y h:i a',strtotime($order['created_at']))}}
                                            </div>
                                            <h5>
                                                <i class="tio-shop"></i>
                                                {{translate('branch')}} : <label class="badge badge-secondary">{{$order->branch?$order->branch->name:'Branch deleted!'}}</label>
                                            </h5>
                                        </div>

                                        @if($order['order_type'] != 'pos')
                                            <div><strong>{{translate('order_Note')}}:</strong> {{$order['order_note']}}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="d-flex flex-column gap-2 align-items-sm-end">
                                        <div class="d-flex gap-2">
                                            <div class="hs-unfold">
                                                @if($order['order_status']=='out_for_delivery')
                                                    @php($origin=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                                    @php($current=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                                    @if(isset($origin))
                                                        <a class="btn btn-outline-primary" target="_blank"
                                                           title="Delivery Boy Last Location" data-toggle="tooltip" data-placement="top"
                                                           href="https://www.google.com/maps/dir/?api=1&origin={{$origin['latitude']}},{{$origin['longitude']}}&destination={{$current['latitude']}},{{$current['longitude']}}">
                                                            <i class="tio-map"></i>
                                                        </a>
                                                    @else
                                                        <a class="btn btn-outline-primary" href="javascript:" data-toggle="tooltip"
                                                           data-placement="top" title="{{translate('Waiting for location...')}}">
                                                            <i class="tio-map"></i>
                                                        </a>
                                                    @endif
                                                @else
                                                    <a class="btn btn-outline-dark last_location_view" href="javascript:"
                                                       data-toggle="tooltip" data-placement="top"
                                                       title= "{{translate('Only available when order is out for delivery!')}}">
                                                        <i class="tio-map"></i>
                                                    </a>
                                                @endif
                                            </div>

                                            <a class="btn btn-primary" target="_blank"
                                                href={{route('admin.orders.generate-invoice',[$order['id']])}}>
                                                <i class="tio-print"></i> {{translate('print_invoice')}}
                                            </a>
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('Order_Status')}}:</div>
                                            @if($order['order_status']=='pending')
                                                <span class="text-info text-capitalize">{{translate('pending')}}</span>
                                            @elseif($order['order_status']=='confirmed')
                                                <span class="text-info text-capitalize">{{translate('confirmed')}}</span>
                                            @elseif($order['order_status']=='processing')
                                                <span class="text-warning text-capitalize">{{translate('processing')}}</span>
                                            @elseif($order['order_status']=='out_for_delivery')
                                                <span class="text-warning text-capitalize">{{translate('out_for_delivery')}}</span>
                                            @elseif($order['order_status']=='delivered')
                                                <span class="text-success text-capitalize">{{translate('delivered')}}</span>
                                            @else
                                                <span class="text-danger text-capitalize">{{str_replace('_',' ',$order['order_status'])}}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('payment_Method')}}:</div>
                                            <div>{{str_replace('_',' ',$order['payment_method'])}}</div>
                                        </div>
                                        @if($order['payment_method'] != 'cash_on_delivery')
                                            <div class="d-flex justify-content-sm-end align-items-center gap-2" >
                                                @if($order['transaction_reference']==null && $order['order_type']!='pos')
                                                    <div>{{translate('reference_Code')}}:</div>
                                                    <button class="btn btn-outline-primary btn-sm py-1" data-toggle="modal"
                                                            data-target=".bd-example-modal-sm">
                                                        {{translate('add')}}
                                                    </button>
                                                @elseif($order['order_type']!='pos')
                                                    <div>{{translate('reference_Code')}}:</div>
                                                    <div>{{$order['transaction_reference']}}</div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('Payment_Status')}}:</div>
                                            @if($order['payment_status']=='paid')
                                                <span class="text-success">{{translate('paid')}}</span>
                                            @else
                                                <span class="text-danger">{{translate('unpaid')}}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-sm-end gap-2">
                                            <div>{{translate('order_Type')}}:</div>
                                            <label class="text-primary">{{str_replace('_',' ',$order['order_type'])}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                                                class="img-fit img-fluid rounded aspect-ratio-1"  alt="{{ translate('image') }}">
                                                        @endif
                                                    </div>
                                                    <div class="media-body">
                                                        <h6 class="mb-1 w-24ch">{{$product['name']}}</h6>
                                                        @if(count(json_decode($detail['variation'],true))>0)
                                                            @foreach(json_decode($detail['variation'],true)[0] ?? json_decode($detail['variation'],true) as $key1 =>$variation)
                                                                <div class="font-size-sm text-body text-capitalize">
                                                                    @if($variation != null)
                                                                        <span>{{$key1}} :  </span>
                                                                    @endif
                                                                    <span class="font-weight-bold">{{$variation}}</span>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ Helpers::set_symbol($detail['price']) }}
                                            </td>
                                            <td>{{Helpers::set_symbol($detail['discount_on_product'])}}</td>
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

                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row">
                                    <dt class="col-6">{{translate('items')}} {{translate('price')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($item_amount) }}</dd>

                                    <dt class="col-6">{{translate('item_discount')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($total_item_discount) }}</dd>

                                    <dt class="col-6">{{translate('tax')}} / {{translate('vat')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($total_tax) }}</dd>

                                    <dt class="col-6">{{translate('subtotal')}}:</dt>
                                    <dd class="col-6 text-end">{{ Helpers::set_symbol($sub_total+$total_tax) }}</dd>
                                    <dt class="col-6">{{translate('coupon')}} {{translate('discount')}}:</dt>
                                    <dd class="col-6 text-end"> - {{ Helpers::set_symbol($order['coupon_discount_amount']) }}</dd>

                                    @if($order['order_type'] == 'pos')
                                        <dt class="col-6">{{translate('Extra Discount')}}:</dt>
                                        <dd class="col-6 text-end"> - {{ Helpers::set_symbol($order['extra_discount']) }}</dd>
                                    @endif
                                    <dt class="col-6">{{translate('delivery')}} {{translate('fee')}}:</dt>
                                    <dd class="col-6 text-end">
                                        @if($order['order_type']=='self_pickup')
                                            @php($del_c=0)
                                        @else
                                            @php($del_c=$order['delivery_charge'])
                                        @endif
                                        {{ Helpers::set_symbol($del_c) }}
                                    </dd>

                                    <dt class="col-6 border-top pt-2 font-weight-bold">{{translate('total')}}:</dt>
                                    <dd class="col-6 text-end border-top pt-2 font-weight-bold">{{ Helpers::set_symbol($sub_total+$del_c+$total_tax-$order['coupon_discount_amount']-$order['extra_discount']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($order->user_id != null)
                <div class="col-lg-4">
                    @if($order['order_type'] != 'pos')
                    <div class="card mb-3">
                        <div class="card-body text-capitalize d-flex flex-column">
                            <h4 class="mb-0 text-center">{{ $order['order_type'] != 'pos' ? translate('Order & Shipping Info ') : translate('Order Info') }}</h4>

                            <div class="mt-2">
                                @if($order['order_type'] != 'pos')
                                    <h6>{{translate('Order Status')}}</h6>
                                    <select name="order_status" onchange="route_alert('{{route('admin.orders.status',['id'=>$order['id']])}}'+'&order_status='+ this.value,'{{translate("Change the order status to ") }}'+  this.value.replace(/_/g, ' '))" class="form-control">
                                        <option value="pending" {{$order['order_status'] == 'pending'? 'selected' : ''}}>{{translate('pending')}}</option>
                                        <option value="confirmed" {{$order['order_status'] == 'confirmed'? 'selected' : ''}}> {{translate('confirmed')}}</option>
                                        <option value="processing" {{$order['order_status'] == 'processing'? 'selected' : ''}}> {{translate('processing')}}</option>
                                        <option value="out_for_delivery" {{$order['order_status'] == 'out_for_delivery'? 'selected' : ''}}>{{translate('Out_For_Delivery')}} </option>
                                        <option value="delivered" {{$order['order_status'] == 'delivered'? 'selected' : ''}}>{{translate('Delivered')}} </option>
                                        <option value="returned" {{$order['order_status'] == 'returned'? 'selected' : ''}}> {{translate('Returned')}}</option>
                                        <option value="failed" {{$order['order_status'] == 'failed'? 'selected' : ''}}>{{translate('Failed')}} </option>
                                        <option value="canceled" {{$order['order_status'] == 'canceled'? 'selected' : ''}}>{{translate('canceled')}} </option>
                                    </select>
                                @endif
                            </div>

                            <div class="mt-3">
                                @if($order['order_type'] != 'pos')
                                    <h6>{{translate('Payment Status')}}</h6>
                                    <select name="order_status" onchange="route_alert('{{route('admin.orders.payment-status',['id'=>$order['id']])}}'+'&payment_status='+ this.value,'{{translate("Change status to ")}}' + this.value)" class="form-control">
                                        <option value="paid" {{$order['payment_status'] == 'paid'? 'selected' : ''}}> {{translate('paid')}}</option>
                                        <option value="unpaid" {{$order['payment_status'] == 'unpaid'? 'selected' : ''}}>{{translate('unpaid')}} </option>
                                    </select>
                                @endif
                            </div>

                            @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                                <div class="mt-3">
                                    <h6>{{translate('Select Deliveryman')}}</h6>
                                    <select class="form-control" name="delivery_man_id" id="select-delivery-man">
                                        <option value="0">{{translate('select')}} {{translate('deliveryman')}}</option>
                                        @foreach($deliverymen as $deliveryman)
                                            <option value="{{$deliveryman['id']}}" {{$order['delivery_man_id']==$deliveryman['id']?'selected':''}}>
                                                {{$deliveryman['f_name'].' '.$deliveryman['l_name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-header-title"><i class="tio tio-user"></i> {{translate('Customer_Information')}}</h4>
                        </div>

                        <div class="card-body">
                            <div class="media gap-3">
                                @if($order->customer)
                                    <div class="avatar-lg rounded-circle">
                                        <img
                                            class="img-fit rounded-circle"
                                            src="{{$order->customer->image_fullpath}}"
                                            alt="Image Description">
                                    </div>
                                    <div class="media-body d-flex flex-column gap-1 text-dark">
                                        <div>{{$order->customer['f_name'].' '.$order->customer['l_name']}}</div>
                                        <div>{{\App\Model\Order::where('user_id',$order['user_id'])->count()}} {{translate('orders')}}</div>
                                        <a class="text-dark" href="tel:{{$order->customer['phone']}}">{{$order->customer['phone']}}</a>
                                        <a class="text-dark" href="mailto:{{$order->customer['email']}}">{{$order->customer['email']}}</a>
                                    </div>
                                @else
                                    <div class="media-body d-flex flex-column gap-1 text-dark">
                                        <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                                {{translate('Customer_deleted')}}
                                            </span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-header-title">
                                    <i class="tio tio-user"></i>
                                    {{translate('Delivery_Address')}}
                                </h4>
                            </div>

                            <div class="card-body">
                                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                                <div class="d-flex justify-content-between gap-3">
                                    @if(isset($address))
                                        <div class="delivery--information-single flex-column flex-grow-1">
                                            <div class="d-flex">
                                                <div class="name">{{translate('name')}}</div>
                                                <div class="info">{{$address['contact_person_name']}}</div>
                                            </div>
                                            <div class="d-flex">
                                                <div class="name">{{translate('contact')}}</div>
                                                <a href="tel:{{$address['contact_person_number']}}" class="info">{{$address['contact_person_number']}}</a>
                                            </div>
                                            @if($address['floor'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('floor')}}</div>
                                                    <div class="info">#{{$address['floor']}}</div>
                                                </div>
                                            @endif
                                            @if($address['house'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('house')}}</div>
                                                    <div class="info">#{{$address['house']}}</div>
                                                </div>
                                            @endif
                                            @if($address['road'])
                                                <div class="d-flex">
                                                    <div class="name">{{translate('road')}}</div>
                                                    <div class="info">#{{$address['road'] }}</div>
                                                </div>
                                            @endif
                                            <hr class="w-100">
                                            <div>
                                                <a target="_blank" class="text-dark d-flex align-items-center gap-3"
                                                   href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                                                    <i class="tio-map"></i> {{$address['address']}}<br>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($address))
                                        <a class="link" data-toggle="modal" data-target="#shipping-address-modal"
                                           href="javascript:"><i class="tio tio-edit"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{translate('reference')}} {{translate('code')}} {{translate('add')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('admin.orders.add-payment-ref-code',[$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="EX : Code123" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">{{translate('submit')}}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0 mb-minus-1px">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"/>
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                                aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                      d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                      <i class="tio-location-search"></i>
                    </span>
                </div>

                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                @if(isset($address))
                    <form action="{{route('admin.order.update-shipping',[$order['delivery_address_id']])}}"
                          method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('type')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                           value="{{$address['address_type']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('contact')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                           value="{{$address['contact_person_number']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('name')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                           value="{{$address['contact_person_name']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('address')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address"
                                           value="{{$address['address']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('road')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="road" value="{{$address['road']}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('house')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="house" value="{{$address['house']}}">
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('floor')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="floor" value="{{$address['floor']}}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('latitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="latitude"
                                           value="{{$address['latitude']}}"
                                           required>
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{translate('longitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="longitude"
                                           value="{{$address['longitude']}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-dismiss="modal">{{translate('close')}}</button>
                            <button type="submit" class="btn btn-primary">{{translate('save')}} {{translate('changes')}}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict"

        $('#select-delivery-man').on('change', function (){
            let id = $(this).val();
            addDeliveryMan(id);
        })

        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/admin/orders/add-delivery-man/{{$order['id']}}/' + id,
                data: $('#product_form').serialize(),
                success: function (data) {
                    if(data.status == true) {
                        toastr.success('{{translate("Delivery man successfully assigned/changed")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.error('{{translate("Deliveryman man can not assign/change in that status")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('{{translate("Add valid data")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        $('.last_location_view').on('click', function (){
            toastr.warning('{{translate("Only available when order is out for delivery!")}}', {
                CloseButton: true,
                ProgressBar: true
            });
        })

    </script>
@endpush
