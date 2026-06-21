@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Thông tin cá nhân</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">{{ auth()->user()->utype === 'ADMM' ? 'Dashboard' : 'Trang cá nhân' }}</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Hồ sơ</div></li>
            </ul>
        </div>

        <div class="wg-box">
            @if(Session::has('status'))
                <p class="alert alert-success">{{ Session::get('status') }}</p>
            @endif

            <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.profile.update') }}">
                @csrf
                @method('PUT')

                <fieldset class="name">
                    <div class="body-title">Họ và tên <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Email <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    <div class="text-tiny mt-2">
                        {{ $user->utype === 'ADMM' ? 'Admin tổng dùng email @lvtn.vn.' : 'Nhân viên dùng email @tttn.vn.' }}
                    </div>
                    @error('email') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Số điện thoại <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" required>
                    @error('mobile') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Ngày tháng năm sinh</div>
                    <input class="flex-grow" type="date" name="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                    @error('birth_date') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Vị trí <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" name="position" value="{{ old('position', $user->position ?? ($user->utype === 'ADMM' ? 'Admin tổng' : 'Nhân viên')) }}" required>
                    @error('position') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Mật khẩu mới</div>
                    <input class="flex-grow" type="password" name="password" autocomplete="new-password">
                    <div class="text-tiny mt-2">Để trống nếu không muốn thay đổi mật khẩu.</div>
                    @error('password') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Xác nhận mật khẩu mới</div>
                    <input class="flex-grow" type="password" name="password_confirmation" autocomplete="new-password">
                </fieldset>

                <div class="bot">
                    <div></div>
                    <button class="tf-button w208" type="submit">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
