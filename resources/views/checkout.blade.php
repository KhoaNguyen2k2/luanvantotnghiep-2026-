@extends('layouts.app')
@section('content')

<main class="pt-90">
    @php
      $vnd = fn($n) => number_format((float) $n, 0, ',', '.') . ' ₫';
      $cartSubtotal = (float) Cart::instance('cart')->content()->reduce(function ($carry, $item) {
          $unit = (float) ($item->model->sale_price ?? $item->model->regular_price ?? $item->price);
          return $carry + ($unit * (int) $item->qty);
      }, 0.0);

      $shipping = 0.0;
      $discountData = Session::get('discounts');
      $discount = $discountData ? (float) ($discountData['discount'] ?? 0) : 0.0;
      $subtotalAfterDiscount = $discountData ? (float) ($discountData['subtotal'] ?? max(0, $cartSubtotal - $discount)) : max(0, $cartSubtotal - $discount);
      $vat = $discountData ? (float) ($discountData['tax'] ?? 0) : ($subtotalAfterDiscount * ((float) config('cart.tax') / 100));
      $total = $discountData ? (float) ($discountData['total'] ?? ($subtotalAfterDiscount + $shipping + $vat)) : ($subtotalAfterDiscount + $shipping + $vat);
      $discountLines = $discountData['discount_lines'] ?? [];
    @endphp
    <div class="mb-4 pb-4"></div>
    <section class="shop-checkout container">
      <h2 class="page-title">Giao hàng và Thanh toán</h2>
      <div class="checkout-steps">
        <a href="{{ route('cart.index') }}" class="checkout-steps__item active">
          <span class="checkout-steps__item-number">01</span>
          <span class="checkout-steps__item-title">
            <span>Giỏ hàng</span>
            <em>Quản lý danh sách sản phẩm</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item active">
          <span class="checkout-steps__item-number">02</span>
          <span class="checkout-steps__item-title">
            <span>Giao hàng và Thanh toán</span>
            <em>Thanh toán danh sách sản phẩm</em>
          </span>
        </a>
        <a href="javascript:void(0)" class="checkout-steps__item">
          <span class="checkout-steps__item-number">03</span>
          <span class="checkout-steps__item-title">
            <span>Xác nhận</span>
            <em>Xem lại và gửi đơn hàng</em>
          </span>
        </a>
      </div>
      <form name="checkout-form" action="{{ route('cart.place.an.order') }}" method="POST">
        @csrf
        <div class="checkout-form">
          <div class="billing-info__wrapper">
            <div class="row">
              <div class="col-6">
                <h4>CHI TIẾT GIAO HÀNG</h4>
              </div>
              <div class="col-6">
              </div>
            </div>


            @if($address)
                <div class="row">
                    <div class="col-mid-12">
                        <div class="my-account__address-list">
                            <div class="my-account__address-list-item">
                                <div class="my-account__address-item__detail">
                                    <p>{{ $address->name }}</p>
                                    <p>{{ $address->address }}</p>
                                    <p>{{ $address->landmark }}</p>
                                    <p>{{ $address->city }}, {{ $address->state }}, {{ $address->country }}</p>
                                    <p>{{ $address->zip }}</p>
                                    <br/>
                                    <p>{{ $address->phone }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            @else
            <div class="row mt-5">
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="name" required="" value="{{ old('name') }}">
                  <label for="name">Họ và tên *</label>
                  @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="phone" required value="{{ old('phone') }}">
                  <label for="phone">Số điện thoại *</label>
                  @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="zip" required value="{{ old('zip') }}">
                  <label for="zip">Mã bưu điện *</label>
                  @error('zip')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating mt-3 mb-3">
                  <input type="text" class="form-control" name="state" required value="{{ old('state') }}">
                  <label for="state">Bang / Tỉnh *</label>
                  @error('state')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="city" required value="{{ old('city') }}">
                  <label for="city">Thị trấn / Thành phố *</label>
                  @error('city')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="address" required value="{{ old('address') }}">
                  <label for="address">Số nhà, Tên tòa nhà *</label>
                  @error('address')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="locality" required value="{{ old('locality') }}">
                  <label for="locality">Tên đường, Khu vực, Khu phố *</label>
                  @error('locality')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating my-3">
                  <input type="text" class="form-control" name="landmark" value="{{ old('landmark') }}">
                  <label for="landmark">Điểm mốc</label>
                  @error('landmark')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
              </div>
            </div>
            @endif
          </div>
          <div class="checkout__totals-wrapper">
            <div class="sticky-content">
              <div class="checkout__totals">
                <h3>Đơn hàng của bạn</h3>
                <table class="checkout-cart-items">
                  <thead>
                    <tr>
                      <th>SẢN PHẨM</th>
                      <th align="right">TẠM TÍNH</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach (Cart::instance('cart')->content() as $item)
                    @php
                      $unit = (float) ($item->model->sale_price ?? $item->model->regular_price ?? $item->price);
                      $line = $unit * (int) $item->qty;
                    @endphp
                    <tr>
                      <td>
                        {{$item->name}} x {{ $item->qty }}
                      </td>
                      <td align="right">
                        {{ $vnd($line) }}
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                <table>
                <table class="checkout-totals">
                  <tbody>
                    <tr>
                      <th>TẠM TÍNH</th>
                      <td align="right">{{ $vnd($cartSubtotal) }}</td>
                    </tr>
                    @if($discount > 0)
                      @foreach($discountLines as $line)
                      <tr>
                        <th>Giảm giá {{ $line['code'] ?? '' }}</th>
                        <td align="right">-{{ $vnd($line['amount'] ?? 0) }}</td>
                      </tr>
                      @endforeach
                      <tr>
                        <th>TẠM TÍNH SAU GIẢM GIÁ</th>
                        <td align="right">{{ $vnd($subtotalAfterDiscount) }}</td>
                      </tr>
                    @endif
                    <tr>
                      <th>VẬN CHUYỂN</th>
                      <td align="right">Miễn phí vận chuyển</td>
                    </tr>
                    <tr>
                      <th>VAT ({{ (float) config('cart.tax') }}%)</th>
                      <td align="right">{{ $vnd($vat) }}</td>
                    </tr>
                    <tr>
                      <th>TỔNG CỘNG</th>
                      <td align="right">{{ $vnd($total) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>


              <div class="checkout__payment-methods">
                <input type="hidden" name="total" value="{{ $total }}" />

                <button type="submit" class="btn btn-success check-out" name="mode" value="vnpay">
                  Thanh toán VNPAY
                </button>

                
                
                <div class="form-check">
                  <input class="form-check-input form-check-input_fill" type="radio" name="mode" id="mode3" value="cod">
                  <label class="form-check-label" for="mode3">
                    Thanh toán khi nhận hàng
                </label>
                </div>



                <div class="policy-text">
                  Dữ liệu cá nhân của bạn sẽ được sử dụng để xử lý đơn hàng, hỗ trợ trải nghiệm của bạn trên trang web này và cho các mục đích khác được mô tả trong <a href="terms.html" target="_blank">chính sách bảo mật</a> của chúng tôi.
                </div>
              </div>
              <button type="submit" class="btn btn-primary btn-checkout" name="mode" value="cod">ĐẶT HÀNG</button>
            </div>
          </div>
        </div>
      </form>
    </section>
  </main>

@endsection