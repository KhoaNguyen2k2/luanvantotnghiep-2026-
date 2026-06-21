@extends('layouts.app')
@section('content')

 <main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
      <h2 class="page-title">Wishlist</h2>

      <div class="shopping-cart">
        @if($items->count() > 0)
        <div class="cart-table__wrapper">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th></th>
                <th>Số lượng</th>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                  $p = $item->model;
                  $image = $p?->image ?: null;
                  $thumbPath = $image ? public_path('uploads/products/thumbnails/' . $image) : null;
                  $imgSrc = ($image && $thumbPath && file_exists($thumbPath))
                    ? asset('uploads/products/thumbnails/' . $image)
                    : ($image ? asset('uploads/products/' . $image) : '');
                @endphp
              <tr>
                <td>
                  <div class="shopping-cart__product-item">
                    @if($imgSrc !== '')
                      <img loading="lazy" src="{{ $imgSrc }}" width="120" height="120" alt="{{ $item->name }}" />
                    @else
                      <div class="bg-light d-flex align-items-center justify-content-center" style="width:120px;height:120px;">
                        <span class="text-secondary">No image</span>
                      </div>
                    @endif
                  </div>
                </td>
                <td>
                  <div class="shopping-cart__product-item__detail">
                    <h4>{{ $item->name }}</h4>
                  </div>
                </td>
                <td>
                  {{ $item->qty }}
                </td>
                <td>
                  <form method="POST" action="{{ route('wishlist.move_to_cart') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <button type="submit" class="btn btn-sm btn-dark text-nowrap">Chuyển qua giỏ hàng</button>
                  </form>
                </td>
                <td>
                  <form method="POST" action="{{ route('wishlist.remove') }}" class="d-inline" onsubmit="return confirm('Xóa sản phẩm khỏi wishlist?');">
                    @csrf
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <button type="submit" class="btn btn-link p-0 remove-cart border-0 bg-transparent" title="Xóa khỏi wishlist" aria-label="Xóa khỏi wishlist">
                      <svg width="10" height="10" viewBox="0 0 10 10" fill="#767676" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                        <path d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                      </svg>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div class="cart-table-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="{{ route('shop.index') }}" class="btn btn-outline-dark">Tiếp tục mua sắm</a>
            <div class="d-flex flex-wrap gap-2 justify-content-end ms-auto">
              <form method="POST" action="{{ route('wishlist.move_all_to_cart') }}" class="m-0 d-inline"
                onsubmit="return confirm('Chuyển toàn bộ sản phẩm trong wishlist sang giỏ hàng?');">
                @csrf
                <button type="submit" class="btn btn-dark">Chuyển tất cả qua giỏ hàng</button>
              </form>
              <form method="POST" action="{{ route('wishlist.clear') }}" class="m-0 d-inline"
                onsubmit="return confirm('Bạn có chắc muốn xoá toàn bộ wishlist?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Xoá tất cả</button>
              </form>
            </div>
          </div>
        </div>
        @else
        <div class="alert alert-secondary mb-0">Wishlist của bạn đang trống.</div>
        <a href="{{ route('shop.index') }}" class="btn btn-dark mt-3">Đến cửa hàng</a>
        @endif
      </div>
    </section>
  </main>


@endsection
