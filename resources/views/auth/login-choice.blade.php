@extends('layouts.app')

@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="login-register container">
        <h2 class="section-title text-center mb-4">Chọn loại tài khoản đăng nhập</h2>

        <div class="row justify-content-center g-4">
            <div class="col-md-5">
                <div class="border rounded p-4 h-100 text-center">
                    <h4 class="mb-3">Khách hàng</h4>
                    <p class="text-secondary">Dành cho tài khoản mua hàng trên website.</p>
                    <a href="{{ route('login.customer') }}" class="btn btn-primary w-100 text-uppercase">Đăng Nhập</a>
                </div>
            </div>
            <div class="col-md-5">
                <div class="border rounded p-4 h-100 text-center">
                    <h4 class="mb-3">Nhân viên / Admin tổng</h4>
                    <p class="text-secondary">Dành cho nhân viên ADM và admin tổng ADMM.</p>
                    <a href="{{ route('login.staff') }}" class="btn btn-outline-primary w-100 text-uppercase">Đăng Nhập</a>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
