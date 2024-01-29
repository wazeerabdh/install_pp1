<div class="table-responsive border-primary-light pos-cart-table rounded">
    <table class="table table-align-middle mb-0">
        <thead class="bg-primary-light text-dark">
            <tr>
                <th class="border-bottom-0">{{translate('item')}}</th>
                <th class="border-bottom-0" class="text-center">{{translate('qty')}}</th>
                <th class="border-bottom-0">{{translate('price')}}</th>
                <th class="border-bottom-0 text-end">{{translate('delete')}}</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $subtotal = 0;
        $discount = 0;
        $discount_type = 'amount';
        $discount_on_product = 0;
        $total_tax = 0;
        ?>
        @if(session()->has('cart') && count( session()->get('cart')) > 0)
            <?php
            $cart = session()->get('cart');
            if (isset($cart['discount'])) {
                $discount = $cart['discount'];
                $discount_type = $cart['discount_type'];
            }
            ?>
            @foreach(session()->get('cart') as $key => $cartItem)
                @if(is_array($cartItem))
                    <?php
                    $product_subtotal = ($cartItem['price']) * $cartItem['quantity'];
                    $discount_on_product += ($cartItem['discount'] * $cartItem['quantity']);
                    $subtotal += $product_subtotal;

                    $product = \App\Model\Product::find($cartItem['id']);
                    $total_tax += Helpers::tax_calculate($product, $cartItem['price']) * $cartItem['quantity'];

                    ?>
                    <tr>
                        <td class="media gap-2 align-items-center">
                            <div class="avatar-sm rounded border">
                                <img class="img-fit rounded"
                                    src="{{$cartItem['image'][0]}}"
                                    alt="{{$cartItem['name']}} image">
                            </div>
                            <div class="media-body">
                                <h5 class="mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                                <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                            </div>
                        </td>
                        <td>
                            <input type="number" data-key="{{$key}}" class="form-control qty"
                                   value="{{$cartItem['quantity']}}" min="1" onkeyup="updateQuantity(event)">
                        </td>
                        <td>
                            <div class="fs-12">
                                {{ Helpers::set_symbol($product_subtotal) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="javascript:removeFromCart({{$key}})" class="btn btn-sm btn-outline-danger"> <i
                                    class="tio-delete-outlined"></i></a>
                        </td>
                    </tr>
                @endif
            @endforeach
        @endif
        </tbody>
    </table>
</div>

<?php
    $total = $subtotal;

    $session_subtotal = $subtotal;
    $session_total = $subtotal+$total_tax-$discount_on_product;
    \Session::put('subtotal', $session_subtotal);
    \Session::put('total', $session_total);

    $discount_amount = ($discount_type == 'percent' && $discount > 0) ? (($total * $discount) / 100) : $discount;
    $discount_amount += $discount_on_product;
    $total -= $discount_amount;

    $extra_discount = session()->get('cart')['extra_discount'] ?? 0;
    $extra_discount_type = session()->get('cart')['extra_discount_type'] ?? 'amount';
    if ($extra_discount_type == 'percent' && $extra_discount > 0) {
        $extra_discount = ($subtotal * $extra_discount) / 100;
    }
    if ($extra_discount) {
        $total -= $extra_discount;
    }
?>
<div class="box p-3">
    <dl class="row">
        <dt class="col-6">{{translate('sub_total')}} :</dt>
        <dd class="col-6 text-end">{{ Helpers::set_symbol($subtotal) }}</dd>
        <dt class="col-6">{{translate('product')}} {{translate('discount')}}:
        </dt>
        <dd class="col-6 text-end">
            - {{ Helpers::set_symbol(round($discount_amount,2)) }}</dd>

        <dt class="col-6">{{translate('extra')}} {{translate('discount')}}:
        </dt>
        <dd class="col-6 text-end">
            <button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-discount"><i
                    class="tio-edit"></i>
            </button>
            - {{ Helpers::set_symbol($extra_discount) }}
        </dd>

        <dt class="col-6">{{translate('tax')}} :</dt>
        <dd class="col-6 text-end">{{ Helpers::set_symbol(round($total_tax,2)) }}</dd>

        <dt class="col-6 font-weight-bold fs-16 border-top pt-2">{{translate('total')}} :</dt>
        <dd class="col-6 text-end font-weight-bold fs-16 border-top pt-2">{{ Helpers::set_symbol(round($total+$total_tax, 2)) }}</dd>
    </dl>

    <form action="{{route('branch.pos.order')}}" id='order_place' method="post">
        @csrf
        <div class="my-4">
            <div class="text-dark d-flex mb-2">{{ translate('Paid By') }}:</div>
            <ul class="list-unstyled option-buttons">
                <li>
                    <input type="radio" id="cash" value="cash" name="type" hidden="" checked="">
                    <label for="cash" class="btn border px-4 mb-0">{{ translate('Cash') }}</label>
                </li>
                <li>
                    <input type="radio" value="card" id="card" name="type" hidden="">
                    <label for="card" class="btn border px-4 mb-0">{{ translate('Card') }}</label>
                </li>
            </ul>
        </div>

        <div class="row g-2">
            <div class="col-sm-6">
                <a href="#" class="btn btn-danger btn-block pos-empty-cart">
                    <i class="fa fa-times-circle"></i> {{translate('Cancel_Order')}} </a>
            </div>
            <div class="col-sm-6">
                <button type="submit" class="btn  btn-primary btn-block"><i class="fa fa-shopping-bag"></i>
                    {{translate('Place_Order')}} </button>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="add-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('update_discount')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('branch.pos.discount')}}" method="post" class="row">
                    @csrf
                    <div class="form-group col-sm-6">
                        <label for="">{{translate('discount')}}</label>
                        <input type="number" value="{{session()->get('cart')['extra_discount'] ?? 0}}"
                               class="form-control" name="discount">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">{{translate('type')}}</label>
                        <select name="type" class="form-control">
                            <option value="amount" {{$extra_discount_type=='amount'?'selected':''}}>{{translate('amount')}}({{Helpers::currency_symbol()}})</option>
                            <option value="percent" {{$extra_discount_type=='percent'?'selected':''}}>{{translate('percent')}}(%)</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add-coupon-discount" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{translate('Coupon_discount')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.pos.discount')}}" method="post" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label for="">{{translate('Coupon_code')}}</label>
                        <input type="number" placeholder="{{translate('SULTAN200')}}" class="form-control">
                    </div>
                    <div class="d-flex justify-content-end col-12">
                        <button class="btn btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="add-tax" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('update_tax')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('branch.pos.tax')}}" method="POST" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label for="">{{translate('tax')}} (%)</label>
                        <input type="number" class="form-control" name="tax" min="0">
                    </div>

                    <div class="form-group col-sm-12">
                        <button class="btn btn-sm btn-primary" type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('payment')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('branch.pos.order')}}" id='order_place' method="post" class="row">
                    @csrf
                    <div class="form-group col-12">
                        <label class="input-label" for="">{{ translate('amount') }}({{Helpers::currency_symbol()}}
                            )</label>
                        <input type="number" class="form-control" name="amount" min="0" step="0.01"
                               value="{{round($total+$total_tax, 2)}}" disabled>
                    </div>
                    <div class="form-group col-12">
                        <label class="input-label" for="">{{translate('type')}}</label>
                        <select name="type" class="form-control">
                            <option value="cash">{{translate('cash')}}</option>
                            <option value="card">{{translate('card')}}</option>
                        </select>
                    </div>
                    <div class="form-group col-12">
                        <button class="btn btn-sm btn-primary"
                                type="submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    $('.pos-empty-cart').on('click', function (){
        emptyCart();
    });
</script>
