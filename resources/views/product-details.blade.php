@extends('layouts.app')

@section('content')
<main class="pt-90">
  <div class="container pt-4 pt-xl-5">
    @if(!$product)
      <div class="alert alert-warning">Sản phẩm không tồn tại hoặc đã bị xóa.</div>
      <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">Quay lại Shop</a>
    @else
      <div class="breadcrumb mb-4">
        <a href="{{ route('home.index') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">Home</a>
        <span class="breadcrumb-separator menu-link fw-medium ps-1 pe-1">/</span>
        <a href="{{ route('shop.index') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">Shop</a>
        <span class="breadcrumb-separator menu-link fw-medium ps-1 pe-1">/</span>
        <span class="menu-link menu-link_us-s text-uppercase fw-medium">{{ $product->name }}</span>
      </div>

      <div class="row g-4">
        <div class="col-12 col-lg-6">
          <div class="bg-body border rounded p-3">
            <img id="mainProductImage" class="w-100 h-auto"
                 src="{{ asset('uploads/products/' . $product->image) }}"
                 alt="{{ $product->name }}">
          </div>

          @if(!empty($product->images))
            <div class="d-flex flex-wrap gap-2 mt-3">
              @foreach(array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $product->images))))) as $gimg)
                <a class="border rounded p-1 bg-body js-product-thumb"
                   href="{{ asset('uploads/products/' . $gimg) }}"
                   data-full="{{ asset('uploads/products/' . $gimg) }}"
                   aria-label="Xem ảnh {{ $product->name }}">
                  <img style="width:84px;height:84px;object-fit:cover"
                       src="{{ asset('uploads/products/thumbnails/' . $gimg) }}"
                       alt="{{ $product->name }}">
                </a>
              @endforeach
            </div>
          @endif
        </div>

        <div class="col-12 col-lg-6">
          <h2 class="mb-2">{{ $product->name }}</h2>
          <div class="text-secondary mb-3">{{ $product->category?->name }} / {{ $product->brand?->name }}</div>

          <div class="fs-4 fw-semibold mb-3">
            @if($product->sale_price)
              <span class="me-2">{{ number_format((float) $product->sale_price, 0, ',', '.') }} ₫</span>
              <s class="text-secondary fs-6">{{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫</s>
            @else
              <span>{{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫</span>
            @endif
          </div>

          <div class="mb-3">
            <div class="fw-medium mb-1">Mô tả ngắn</div>
            <div class="text-secondary">{!! nl2br(e($product->short_description)) !!}</div>
          </div>

          <div class="mb-4">
            <div class="fw-medium mb-1">Chi tiết</div>
            <div class="text-secondary">{!! $product->description !!}</div>
          </div>

        </div>
      </div>

      <!-- Sản phẩm liên quan -->
      @if($relatedProducts->count() > 0)
        <div class="row mt-5 pt-4 border-top">
          <div class="col-12">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
          </div>
          @foreach($relatedProducts as $relatedProduct)
            <div class="col-6 col-md-4 col-lg-3 mb-4">
              <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                <div class="position-relative overflow-hidden bg-light" style="height: 250px;">
                  <img class="card-img-top w-100 h-100" 
                       style="object-fit: cover;" 
                       src="{{ asset('uploads/products/' . $relatedProduct->image) }}"
                       alt="{{ $relatedProduct->name }}">
                  @if($relatedProduct->sale_price)
                    <div class="position-absolute top-0 end-0 m-2">
                      <span class="badge bg-danger">
                        {{ round((1 - ($relatedProduct->sale_price / $relatedProduct->regular_price)) * 100) }}%
                      </span>
                    </div>
                  @endif
                </div>
                <div class="card-body">
                  <h6 class="card-title text-truncate">
                    <a href="{{ route('shop.product.details', ['product_slug' => $relatedProduct->slug]) }}" 
                       class="text-decoration-none text-dark">
                      {{ $relatedProduct->name }}
                    </a>
                  </h6>
                  <div class="text-secondary small mb-2">{{ $relatedProduct->category?->name }}</div>
                  <div class="fs-6 fw-semibold">
                    @if($relatedProduct->sale_price)
                      <span style ="color: blue" >{{ number_format((float) $relatedProduct->sale_price, 0, ',', '.') }} ₫</span>
                      <s style="color: red" >{{ number_format((float) $relatedProduct->regular_price, 0, ',', '.') }} ₫</s>
                    @else
                      <span style="color: blue">{{ number_format((float) $relatedProduct->regular_price, 0, ',', '.') }} ₫</span>
                    @endif
                  </div>
                </div>
                <div class="card-footer bg-white border-top p-2">
                  <a href="{{ route('shop.product.details', ['product_slug' => $relatedProduct->slug]) }}" 
                     class="btn btn-sm btn-outline-primary w-100">
                    Xem chi tiết
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    @endif
  </div>
</main>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const main = document.getElementById('mainProductImage');
    if (!main) return;

    document.querySelectorAll('.js-product-thumb').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        const full = el.getAttribute('data-full');
        if (full) main.setAttribute('src', full);
      });
    });
  });
</script>
@endpush

