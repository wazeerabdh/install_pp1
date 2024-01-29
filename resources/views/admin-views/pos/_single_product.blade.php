<div class="pos-product-item card pos-single-product-card" data-id="{{$product->id}}">
    <div class="pos-product-item_thumb">
        <img src="{{$product['image_fullpath'][0]}}"
                 class="img-fit"
             alt="{{ translate('product') }}">
    </div>

    <div class="pos-product-item_content clickable">
        <div class="pos-product-item_title">
            {{ Str::limit($product['name'], 15) }}
        </div>
        <div class="pos-product-item_price">
            {{ Helpers::set_symbol($product['price']- Helpers::discount_calculate($product, $product['price'])) }}
        </div>
    </div>
</div>


