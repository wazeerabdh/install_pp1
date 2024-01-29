<div class="modal-header p-2">
    <h4 class="modal-title product-title">
    </h4>
    <button class="close call-when-done" type="button" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="media flex-wrap gap-3">
        <div class="box-120 rounded border">
            <img class="img-fit rounded"
                 src="{{$product['image_fullpath'][0]}}"
                 data-zoom="{{$product['image_fullpath'][0]}}"
                 alt="{{ translate('Product image') }}">
            <div class="cz-image-zoom-pane"></div>
        </div>
        <div class="details media-body">
            <h5 class="product-name"><a href="#" class="h3 mb-2 product-title">{{ Str::limit($product->name, 26) }}</a></h5>

            <div class="mb-2">
                <span class="h5 font-weight-normal text-accent mr-1">
                    {{ Helpers::set_symbol($product['price']- Helpers::discount_calculate($product, $product['price'])) }}
                </span>
            </div>

            @if($product->discount > 0)
                <div class="mb-0 text-dark">
                    <strong>{{translate('Discount :')}}</strong>
                    <strong
                        id="set-discount-amount">{{ Helpers::set_symbol(Helpers::discount_calculate($product, $product->price)) }}</strong>
                </div>
            @endif
        </div>
    </div>
    <div class="row pt-4">
        <div class="col-12">
            <?php
            $cart = false;
            if (session()->has('cart')) {
                foreach (session()->get('cart') as $key => $cartItem) {
                    if (is_array($cartItem) && $cartItem['id'] == $product['id']) {
                        $cart = $cartItem;
                    }
                }
            }

            ?>
            <h2>{{translate('description')}}</h2>
            <span class="d-block text-dark">
                {!! $product->description !!}
            </span>
            <form id="add-to-cart-form" class="mb-2">
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                @foreach (json_decode($product->choice_options) as $key => $choice)

                    <h3 class="mb-2 pt-4">{{ $choice->title }}</h3>

                    <div class="d-flex gap-3 flex-wrap">
                        @foreach ($choice->options as $key => $option)
                            <input class="btn-check" type="radio"
                                   id="{{ $choice->name }}-{{ $option }}"
                                   name="{{ $choice->name }}" value="{{ $option }}"
                                   @if($key == 0) checked @endif autocomplete="off">
                            <label class="check-label rounded px-2 py-1 text-center lh-1.3 mb-0 choice-input"
                                   for="{{ $choice->name }}-{{ $option }}">{{ $option }}</label>
                        @endforeach
                    </div>
                @endforeach

                <div class="d-flex justify-content-between mt-4">
                    <h3 class="product-description-label mb-0 text-dark">{{translate('Quantity')}}:</h3>

                    <div class="product-quantity d-flex align-items-center">
                        <div class="product-quantity-group" id="quantity_div">
                            <button class="btn btn-number p-2 text-dark" type="button"
                                    data-type="minus" data-field="quantity"
                                    disabled="disabled">
                                    <i class="tio-remove font-weight-bold"></i>
                            </button>

                            <input type="text" name="quantity" id="quantity"
                                   class="form-control input-number text-center cart-qty-field"
                                   placeholder="1" value="1" min="1">

                            <button class="btn btn-number p-2 text-dark" type="button" data-type="plus"
                                    data-field="quantity">
                                    <i class="tio-add  font-weight-bold"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters mt-3 text-dark" id="chosen_price_div">
                    <div class="col-2">
                        <div class="product-description-label">{{translate('Total Price')}}:</div>
                    </div>
                    <div class="col-10">
                        <div class="product-price">
                            <strong id="chosen_price"></strong> {{ Helpers::currency_symbol() }}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    <button class="btn btn-primary add-to-shopping-cart"
                            type="button">
                        <i class="tio-shopping-cart"></i>
                        {{translate('add')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    "use strict"

    cartQuantityInitialize();
    getVariantPrice();
    $('#add-to-cart-form input').on('change', function () {
        getVariantPrice();
    });

    $('.add-to-shopping-cart').on('click', function (){
        addToCart();
    });
</script>

