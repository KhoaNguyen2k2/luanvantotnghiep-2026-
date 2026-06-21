<fieldset class="name">
    <div class="body-title">Họ và tên <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="name" value="{{ old('name', optional($staffMember)->name) }}" required>
    @error('name') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">Email nhân viên <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="email" name="email" value="{{ old('email', optional($staffMember)->email) }}" required>
    <div class="text-tiny mt-2">Email nhân viên bắt buộc có đuôi @tttn.vn.</div>
    @error('email') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">Số điện thoại <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="mobile" value="{{ old('mobile', optional($staffMember)->mobile) }}" required>
    @error('mobile') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">Ngày tháng năm sinh</div>
    <input class="flex-grow" type="date" name="birth_date" value="{{ old('birth_date', optional(optional($staffMember)->birth_date)->format('Y-m-d')) }}">
    @error('birth_date') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">Vị trí <span class="tf-color-1">*</span></div>
    <input class="flex-grow" type="text" name="position" value="{{ old('position', optional($staffMember)->position ?? 'Nhân viên') }}" required>
    @error('position') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">{{ $staffMember ? 'Mật khẩu mới' : 'Mật khẩu' }} <span class="tf-color-1">{{ $staffMember ? '' : '*' }}</span></div>
    <input class="flex-grow" type="password" name="password" autocomplete="new-password" {{ $staffMember ? '' : 'required' }}>
    @if($staffMember)
        <div class="text-tiny mt-2">Để trống nếu không muốn đổi mật khẩu.</div>
    @endif
    @error('password') <span class="alert alert-danger text-center d-block">{{ $message }}</span> @enderror
</fieldset>

<fieldset class="name">
    <div class="body-title">Xác nhận mật khẩu</div>
    <input class="flex-grow" type="password" name="password_confirmation" autocomplete="new-password" {{ $staffMember ? '' : 'required' }}>
</fieldset>

<div class="bot">
    <div></div>
    <button class="tf-button w208" type="submit">{{ $submitLabel }}</button>
</div>
