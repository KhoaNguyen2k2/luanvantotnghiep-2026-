<article class="kcc-product">
  <a class="kcc-product__image" href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
    <img loading="lazy" src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}">
  </a>
  <div class="kcc-product__body">
    <h3 class="kcc-product__title">
      <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">{{ $product->name }}</a>
    </h3>
    <div class="kcc-product__price">
      @if($product->sale_price)
        <s>{{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫</s>
        {{ number_format((float) $product->sale_price, 0, ',', '.') }} ₫
      @else
        {{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫
      @endif
    </div>
  </div>
</article>
