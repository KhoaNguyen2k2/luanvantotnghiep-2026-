```php
@extends('layouts.admin')
@section('content')

<div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>Thông tin mã giảm giá</h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li>
                                            <a href="{{ route('admin.index') }}">
                                                <div class="text-tiny">Bảng điều khiển</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.coupons') }}">
                                                <div class="text-tiny">Mã giảm giá</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Sửa mã giảm giá</div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="wg-box">
                                    <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.coupon.update', ['id' => $coupon->id]) }}">
                                        @csrf
                                        @method('PUT')
                                        <fieldset class="name">
                                            <div class="body-title">Mã giảm giá <span class="tf-color-1">*</span></div>
                                            <input class="flex-grow" type="text" placeholder="Mã giảm giá" name="code" tabindex="0" value="{{ $coupon->code }}" aria-required="true" required="">
                                        </fieldset>
                                        @error('code') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        <fieldset class="category">
                                            <div class="body-title">Loại mã giảm giá</div>
                                            <div class="select flex-grow">
                                                <select class="" name="type">
                                                    <option value="">Chọn</option>
                                                    <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Giảm cố định</option>
                                                    <option value="percent" {{ $coupon->type == 'percent' ? 'selected' : '' }}>Giảm theo phần trăm</option>
                                                </select>
                                            </div>
                                        </fieldset>
                                        @error('type') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        <fieldset class="category">
                                            <div class="body-title">Phạm vi áp dụng <span class="tf-color-1">*</span></div>
                                            <div class="select flex-grow">
                                                <select name="scope" id="js-coupon-scope" required>
                                                    <option value="">Chọn phạm vi</option>
                                                    <option value="order" {{ old('scope', $coupon->scope ?? 'order') == 'order' ? 'selected' : '' }}>Toàn bộ hoá đơn</option>
                                                    <option value="category" {{ old('scope', $coupon->scope ?? 'order') == 'category' ? 'selected' : '' }}>Theo danh mục (RAM, CPU, VGA…)</option>
                                                </select>
                                            </div>
                                            <div class="body-text text-tiny mt-2">“Giá trị giỏ hàng” tối thiểu được tính trên tổng phần áp dụng (cả đơn hoặc chỉ các sản phẩm trong danh mục đã chọn).</div>
                                        </fieldset>
                                        @error('scope') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        <div id="js-coupon-category-wrap" class="js-coupon-category-block" @if(old('scope', $coupon->scope ?? 'order') !== 'category') style="display: none" @endif>
                                        <fieldset class="category">
                                            <div class="body-title">Danh mục (khi chọn “Theo danh mục”) <span class="tf-color-1">*</span></div>
                                            <div class="select flex-grow">
                                                <select name="category_id" id="js-coupon-category">
                                                    <option value="">— Chọn danh mục —</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ (string) old('category_id', $coupon->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </fieldset>
                                        @error('category_id') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        </div>
                                        <fieldset class="name">
                                            <div class="body-title">Giá trị <span class="tf-color-1">*</span></div>
                                            <input class="flex-grow" type="text" placeholder="Giá trị mã giảm giá" name="value" tabindex="0" value="{{ $coupon->value }}" aria-required="true" required="">
                                        </fieldset>
                                        @error('value') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        <fieldset class="name">
                                            <div class="body-title">Giá trị giỏ hàng <span class="tf-color-1">*</span></div>
                                            <input class="flex-grow" type="text" placeholder="Giá trị giỏ hàng" name="cart_value" tabindex="0" value="{{ $coupon->cart_value }}" aria-required="true" required="">
                                        </fieldset>
                                        @error('cart_value') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror
                                        <fieldset class="name">
                                            <div class="body-title">Ngày hết hạn <span class="tf-color-1">*</span></div>
                                            <input class="flex-grow" type="date" placeholder="Ngày hết hạn" name="expiry_date" tabindex="0" value="{{ old('expiry_date', $coupon->expiry_date?->format('Y-m-d')) }}" aria-required="true" required="">
                                        </fieldset>
                                        @error('expiry_date') <span class="alert alert-danger text-center">{{ $message }}</span> @enderror

                                        <div class="bot">
                                            <div></div>
                                            <button class="tf-button w208" type="submit">Lưu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

<script>
(function () {
    var scope = document.getElementById('js-coupon-scope');
    var wrap = document.getElementById('js-coupon-category-wrap');
    var cat = document.getElementById('js-coupon-category');
    if (!scope || !wrap) return;
    function sync() {
        var show = scope.value === 'category';
        wrap.style.display = show ? '' : 'none';
        if (cat) {
            cat.disabled = !show;
        }
    }
    scope.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
```
