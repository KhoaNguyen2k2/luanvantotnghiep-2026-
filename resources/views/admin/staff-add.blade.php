@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Thêm nhân viên</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.staff') }}"><div class="text-tiny">Nhân viên</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Thêm mới</div></li>
            </ul>
        </div>

        <div class="wg-box">
            <form class="form-new-product form-style-1" method="POST" action="{{ route('admin.staff.store') }}">
                @csrf
                @include('admin.staff-form', ['staffMember' => null, 'submitLabel' => 'Thêm nhân viên'])
            </form>
        </div>
    </div>
</div>
@endsection
