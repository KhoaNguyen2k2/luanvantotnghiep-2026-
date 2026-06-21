@extends('layouts.app')
@section('content')

<style>
  .text-success {
    color: #28a745 !important;
  }
  .cart-applied-chip .btn-link {
    line-height: 1;
    font-size: 1.15rem;
    min-width: 1.25rem;
  }
  .cart-discount-remove .btn {
    font-size: 0.75rem;
  }
</style>
    
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
      <h2 class="page-title">Cart</h2>
      <div class="checkout-steps">
        <a href="javascript:void(0)" class="checkout-steps__item active">
          <span class="checkout-steps__item-number">01</span>
          <span class="checkout-steps__item-title">
            <span>Giỏ hàng</span>
            <em>Sắp xếp giỏ hàng</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item">
          <span class="checkout-steps__item-number">02</span>
          <span class="checkout-steps__item-title">
            <span>Thanh Toán</span>
            <em>Thanh toán giỏ hàng của bạn</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item">
          <span class="checkout-steps__item-number">03</span>
          <span class="checkout-steps__item-title">
            <span>Xác nhận</span>
            <em>Xem lại và gửi đơn hàng của bạn</em>
          </span>
        </a>
      </div>
      <div class="shopping-cart">
        @php
          $vnd = function ($value) {
            // Accept numeric or formatted strings. Treat commas as thousands separators.
            $raw = is_numeric($value) ? (string) $value : (string) ($value ?? '');
            $raw = str_replace(['₫', 'đ', 'Đ', ' '], '', $raw);
            $raw = str_replace(',', '', $raw); // 34,000.00 -> 34000.00
            // If value contains multiple dots, keep the last one as decimal separator.
            if (substr_count($raw, '.') > 1) {
              $parts = explode('.', $raw);
              $dec = array_pop($parts);
              $raw = preg_replace('/\D+/', '', implode('', $parts)) . '.' . preg_replace('/\D+/', '', $dec);
            }
            $num = (float) preg_replace('/[^\d.]/', '', $raw);
            return number_format($num, 0, ',', '.') . ' ₫';
          };

          $cartSubtotal = 0.0;
          $vat = 0.0; // nếu bạn muốn tính VAT %, mình sẽ đổi theo cấu hình sau
          $shipping = 0.0;
        @endphp
        @if($items -> count() > 0)
        <div class="cart-table__wrapper">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th></th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Tạm tính</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                  $p = $item->model; // Product model (associate)
                  $image = $p?->image ?: null;
                  $thumbPath = $image ? public_path('uploads/products/thumbnails/' . $image) : null;
                  $imgSrc = ($image && $thumbPath && file_exists($thumbPath))
                    ? asset('uploads/products/thumbnails/' . $image)
                    : ($image ? asset('uploads/products/' . $image) : '');

                  // Prefer sale price if present, fallback to cart price.
                  $unit = $p?->sale_price ?: ($p?->regular_price ?: $item->price);
                  $line = ((float) $unit) * ((int) $item->qty);
                  $cartSubtotal += $line;
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
                    <ul class="shopping-cart__product-item__options">
                      <li>SKU: {{ $p?->SKU ?: 'NA' }}</li>
                      <li>Danh mục: {{ $p?->category?->name ?: 'NA' }}</li>
                      <li>Thương hiệu: {{ $p?->brand?->name ?: 'NA' }}</li>
                    </ul>
                  </div>
                </td>
                <td>
                  <span class="shopping-cart__product-price">{{ $vnd($unit) }}</span>
                </td>
                <td>
                  <div class="qty-control position-relative">
                    <input type="number" name="quantity" value="{{ $item->qty }}" min="1" class="qty-control__number text-center">
                    <form method="POST" action="{{ route('cart.qty.decrease', ['rowId' => $item->rowId]) }}">
                      @csrf
                      @method('PUT')
                    <div class="qty-control__reduce">-</div>
                    </form>

                    <form method="POST" action="{{ route('cart.qty.increase', ['rowId' => $item->rowId]) }}" >
                      @csrf
                      @method('PUT')
                    <div class="qty-control__increase">+</div>
                    </form>
                  </div>
                </td>
                <td>
                  <span class="shopping-cart__subtotal">{{ $vnd($line) }}</span>
                </td>
                <td>
                  <form method="POST" action="{{ route('cart.item.remove', ['rowId' => $item->rowId]) }}">
                    @csrf
                    @method('DELETE')
                  <a href="javascript:void(0)" class="remove-cart">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="#767676" xmlns="http://www.w3.org/2000/svg">
                      <path d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                      <path d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                    </svg>
                  </a>
                  </form>
                </td>
              </tr>
                @endforeach
             
            </tbody>
          </table>
          <div class="cart-table-footer">
            <form action="{{ route('cart.coupon.apply') }}" method="POST" class="position-relative bg-body">
              @csrf
              <input class="form-control" type="text" name="coupon_code" placeholder="Nhập mã giảm giá" value="" autocomplete="off">
              <input class="btn-link fw-medium position-absolute top-0 end-0 h-100 px-4" type="submit" value="Áp dụng">
            </form>

            @if(!empty($appliedCoupons))
            <div class="cart-applied-coupons mt-3 mb-1 w-100">
              <div class="text-secondary small mb-2">Mã đang áp dụng:</div>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                @foreach($appliedCoupons as $ac)
                  <div class="cart-applied-chip border rounded px-2 py-1 d-flex align-items-center gap-1 bg-light">
                    <span class="fw-medium">{{ $ac['code'] ?? '' }}</span>
                    <form method="POST" action="{{ route('cart.coupon.remove') }}" class="mb-0 d-inline">
                      @csrf
                      <input type="hidden" name="code" value="{{ $ac['code'] ?? '' }}">
                      <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none" title="Gỡ mã {{ $ac['code'] ?? '' }}">×</button>
                    </form>
                  </div>
                @endforeach
              </div>
            </div>
            @endif

            <form action="{{ route('cart.empty') }}" method="POST">
              @csrf
              @method('DELETE')
            <button class="btn btn-light" type="submit">Trống giỏ hàng</button>
            </form>
          </div>
          <div>
            @if(Session::has('success'))
              <p class="text-success">{{ Session::get('success') }}</p>
            @elseif(Session::has('error'))
              <p class="text-danger">{{ Session::get('error') }}</p>
            @endif
          </div>
        </div>
        <div class="shopping-cart__totals-wrapper">
          <div class="sticky-content">
            <div class="shopping-cart__totals">
              <h3>Tổng giỏ hàng</h3>
              @if(Session::has('discounts'))
                @php
                  $disc = Session::get('discounts', []);
                  $discLines = $disc['discount_lines'] ?? [];
                @endphp
                <table class="cart-totals">
                <tbody>
                  <tr>
                    <th>Tạm tính</th>
                    <td>{{ $vnd($cartSubtotal) }}</td>
                  </tr>
                  <tr>
                    <th class="align-top">
                      @if(count($discLines) <= 1)
                        <div class="d-flex align-items-center flex-wrap gap-2">
                          <span>Giảm giá @if(!empty($discLines[0]['code'])) {{ $discLines[0]['code'] }} @endif</span>
                          @if(!empty($discLines[0]['code']))
                          <form method="POST" action="{{ route('cart.coupon.remove') }}" class="cart-discount-remove mb-0 d-inline">
                            @csrf
                            <input type="hidden" name="code" value="{{ $discLines[0]['code'] }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2">Gỡ mã</button>
                          </form>
                          @endif
                        </div>
                      @else
                        <details class="cart-discount-details">
                          <summary class="cursor-pointer user-select-none" style="cursor:pointer;">Giảm giá <span class="text-secondary">({{ count($discLines) }} mã)</span> — bấm để xem chi tiết</summary>
                          <ul class="mt-2 mb-0 ps-0 small text-start fw-normal list-unstyled">
                            @foreach($discLines as $ln)
                              <li class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                <span><span class="fw-medium">{{ $ln['code'] ?? '—' }}</span>: −{{ $vnd($ln['amount'] ?? 0) }}</span>
                                @if(!empty($ln['code']))
                                <form method="POST" action="{{ route('cart.coupon.remove') }}" class="cart-discount-remove mb-0">
                                  @csrf
                                  <input type="hidden" name="code" value="{{ $ln['code'] }}">
                                  <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2">Gỡ</button>
                                </form>
                                @endif
                              </li>
                            @endforeach
                          </ul>
                        </details>
                      @endif
                    </th>
                    <td class="align-top text-danger">−{{ $vnd($disc['discount'] ?? 0) }}</td>
                  </tr>
                  <tr>
                    <th>Tổng phụ sau khi giảm giá</th>
                    <td>{{ $vnd(Session::get('discounts')['subtotal']) }}</td>
                  </tr>
                  <tr>
                    <th>Vận chuyển</th>
                    <td>{{ $shipping == 0 ? 'Miễn phí' : $vnd($shipping) }}</td>
                  </tr>
                  <tr>
                    <th>VAT</th>
                    <td>{{ $vnd(Session::get('discounts')['tax']) }}</td>
                  </tr>
                  <tr>
                    <th>Total</th>
                    <td>{{ $vnd(Session::get('discounts')['total']) }}</td>
                  </tr>
                </tbody>
              </table>
              @else
              <table class="cart-totals">
                <tbody>
                  <tr>
                    <th>Tạm tính</th>
                    <td>{{ $vnd($cartSubtotal) }}</td>
                  </tr>
                  <tr>
                    <th>Vận chuyển</th>
                    <td>{{ $shipping == 0 ? 'Miễn phí' : $vnd($shipping) }}</td>
                  </tr>
                  <tr>
                    <th>VAT</th>
                    <td>{{ $vnd($vat) }}</td>
                  </tr>
                  <tr>
                    <th>Total</th>
                    <td>{{ $vnd($cartSubtotal + $shipping + $vat) }}</td>
                  </tr>
                </tbody>
              </table>
              @endif
            </div>
            <div class="mobile_fixed-btn_wrapper">
              <div class="button-wrapper container">
                <a href="{{ route('cart.checkout') }}" class="btn btn-primary btn-checkout">Tiến hành thanh toán</a>
              </div>
            </div>
          </div>
        </div>
            @else
                <div class="row">
                <div class="col-md-12 text-center pt-5 bp-5">
                    <p>Giỏ hàng của bạn đang trống.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-info">Tiếp tục mua sắm</a>
                </div>
                </div>
            @endif
      </div>
    </section>
  </main>

@endsection

@push('scripts')
<script>
  $(function(){
    $(".qty-control__increase").on('click', function(){
      $(this).closest('form').submit();
    });

    $(".qty-control__reduce").on('click', function(){
      $(this).closest('form').submit();
    });

    $('.remove-cart').on('click', function(){
      $(this).closest('form').submit();
    });
  })
</script>
@endpush