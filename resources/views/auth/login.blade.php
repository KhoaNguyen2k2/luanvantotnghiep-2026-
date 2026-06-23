@extends('layouts.app')

@section('content')

<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register container">
      <ul class="nav nav-tabs mb-5" id="login_register" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link nav-link_underscore active" id="login-tab" data-bs-toggle="tab" href="#tab-item-login"
            role="tab" aria-controls="tab-item-login" aria-selected="true">Login</a>
        </li>
      </ul>
      <div class="tab-content pt-2" id="login_register_tab_content">
        <div class="tab-pane fade show active" id="tab-item-login" role="tabpanel" aria-labelledby="login-tab">
          <div class="login-form">
            <form method="POST" action="{{route('login')}}" name="login-form" class="needs-validation" novalidate="">
                @csrf
              <input type="hidden" name="login_type" value="{{ $loginType ?? 'customer' }}">
              <h4 class="mb-4">{{ ($loginType ?? 'customer') === 'staff' ? 'Đăng nhập nhân viên / admin tổng' : 'Đăng nhập khách hàng' }}</h4>
              <div class="form-floating mb-3">
                <input class="form-control form-control_gray @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required="" autocomplete="email" autofocus="">
                <label for="email">Email address *</label>
                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
              </div>

              <div class="pb-3"></div>

              <div class="form-floating mb-3">
                <input id="password" type="password" class="form-control form-control_gray @error('password') is-invalid @enderror " name="password" required="" autocomplete="current-password">
                <label for="customerPasswodInput">Password *</label>
                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
              </div>

              <button class="btn btn-primary w-100 text-uppercase" type="submit">Log In</button>

              @if(($loginType ?? 'customer') === 'customer')
                <div class="login-divider text-center my-3">
                  <span>hoặc</span>
                </div>

                <div class="social-login-grid">
                  <a class="social-login-btn social-login-btn--google" href="{{ route('social.redirect', ['provider' => 'google']) }}">
                    <i class="fa fa-google"></i>
                    <span>Google</span>
                  </a>
                  <a class="social-login-btn social-login-btn--facebook" href="{{ route('social.redirect', ['provider' => 'facebook']) }}">
                    <i class="fa fa-facebook"></i>
                    <span>Facebook</span>
                  </a>
                </div>
              @endif

              <div class="customer-option mt-4 text-center">
                <a href="{{route('login')}}" class="btn-text">Chọn loại tài khoản khác</a>
                @if(($loginType ?? 'customer') === 'customer')
                  <span class="text-secondary"> | Chưa có tài khoản?</span>
                  <a href="{{route('register')}}" class="btn-text js-show-register">Tạo tài khoản</a>
                @endif
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

@endsection

@push('styles')
<style>
  .login-divider {
    color: #6b7280;
    font-size: 14px;
    position: relative;
  }

  .login-divider::before,
  .login-divider::after {
    content: "";
    display: inline-block;
    width: 34%;
    height: 1px;
    background: #e5e7eb;
    vertical-align: middle;
  }

  .login-divider span {
    display: inline-block;
    padding: 0 12px;
  }

  .social-login-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .social-login-btn {
    min-height: 46px;
    border: 1px solid #e5e7eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border-radius: 4px;
    font-weight: 700;
    color: #111827;
    background: #fff;
  }

  .social-login-btn:hover {
    color: #111827;
    border-color: #111827;
  }

  .social-login-btn--google i {
    color: #ea4335;
  }

  .social-login-btn--facebook {
    background: #1877f2;
    border-color: #1877f2;
    color: #fff;
  }

  .social-login-btn--facebook:hover {
    color: #fff;
    border-color: #0f5fc9;
    background: #0f5fc9;
  }

  @media (max-width: 575.98px) {
    .social-login-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush
