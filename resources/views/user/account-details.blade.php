@extends('layouts.app')
@section('content')
<main class="pt-90">
  <div class="mb-4 pb-4"></div>
  <section class="my-account container">
    <h2 class="page-title">Thông tin tài khoản</h2>
    <div class="row">
      <div class="col-lg-3">
        @include('user.account-nav')
      </div>
      <div class="col-lg-9">
        @if (session('success'))
          <p class="alert alert-success">{{ session('success') }}</p>
        @endif
        <p class="text-secondary mb-4">Cập nhật thông tin để dễ dàng đặt đơn.</p>

        <form method="POST" action="{{ route('user.account.details.update') }}" class="checkout-form">
          @csrf
          @method('PUT')

          <div class="row mt-2">
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="name" required value="{{ old('name', $user->name) }}">
                <label>Họ tên *</label>
                @error('name')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="email" class="form-control" name="email" required value="{{ old('email', $user->email) }}">
                <label>Email *</label>
                @error('email')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="mobile" required value="{{ old('mobile', $user->mobile) }}">
                <label>Số điện thoại (tài khoản, duy nhất) *</label>
                @error('mobile')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="phone" required value="{{ old('phone', $address->phone ?? '') }}">
                <label>Số điện thoại nhận hàng *</label>
                @error('phone')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="zip" required value="{{ old('zip', $address->zip ?? '') }}">
                <label>Mã bưu điện *</label>
                @error('zip')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="state" required value="{{ old('state', $address->state ?? '') }}">
                <label>Tỉnh / Bang *</label>
                @error('state')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="city" required value="{{ old('city', $address->city ?? '') }}">
                <label>Thành phố *</label>
                @error('city')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="address" required value="{{ old('address', $address->address ?? '') }}">
                <label>Số nhà, tòa nhà *</label>
                @error('address')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="locality" required value="{{ old('locality', $address->locality ?? '') }}">
                <label>Đường, khu phố, phường *</label>
                @error('locality')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="landmark" value="{{ old('landmark', $address->landmark ?? '') }}">
                <label>Điểm mốc / ghi chú địa chỉ</label>
                @error('landmark')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating my-3">
                <input type="text" class="form-control" name="country" value="{{ old('country', $address->country ?? 'Việt Nam') }}">
                <label>Quốc gia</label>
                @error('country')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>

            <div class="col-12"><hr><h5 class="h6">Đổi mật khẩu (tùy chọn)</h5></div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="password" class="form-control" name="password" autocomplete="new-password" value="">
                <label>Mật khẩu mới</label>
                @error('password')<span class="text-danger d-block small">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating my-3">
                <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password" value="">
                <label>Nhập lại mật khẩu</label>
              </div>
       
            </div>
            <button type="submit" class="btn btn-primary">Lưu</button>
          </div>
          
          
        </form>
      </div>
    </div>
  </section>
</main>
@endsection
