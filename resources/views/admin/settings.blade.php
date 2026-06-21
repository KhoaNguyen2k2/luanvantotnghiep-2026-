@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Cài đặt</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.profile') }}">
                        <div class="text-tiny">Trang cá nhân</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Cài đặt</div></li>
            </ul>
        </div>

        <div class="wg-box">
            @if(Session::has('status'))
                <p class="alert alert-success">{{ Session::get('status') }}</p>
            @endif

            <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <fieldset class="name">
                    <div class="body-title">Giới hạn banner trang chủ <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="number" name="home_banner_limit" min="1" max="5" value="{{ old('home_banner_limit', $settings['home_banner_limit']) }}" required>
                    <div class="text-tiny mt-2">Nhập từ 1 đến 5 banner.</div>
                    @error('home_banner_limit')
                        <span class="alert alert-danger text-center d-block">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Giới hạn banner trang mua sắm <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="number" name="shop_banner_limit" min="1" max="5" value="{{ old('shop_banner_limit', $settings['shop_banner_limit']) }}" required>
                    <div class="text-tiny mt-2">Banner mua sắm là banner riêng của trang shop, không dùng chung banner trang chủ.</div>
                    @error('shop_banner_limit')
                        <span class="alert alert-danger text-center d-block">{{ $message }}</span>
                    @enderror
                </fieldset>

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">Lưu cài đặt</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
