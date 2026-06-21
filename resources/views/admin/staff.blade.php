@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Quản lý nhân viên</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Nhân viên</div></li>
            </ul>
        </div>

        <div class="wg-box">
            @if(Session::has('status'))
                <p class="alert alert-success">{{ Session::get('status') }}</p>
            @endif

            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="text-tiny">Tổng nhân viên: {{ $staff->total() }}</div>
                <a class="tf-button style-1 w208" href="{{ route('admin.staff.add') }}">
                    <i class="icon-plus"></i>Thêm nhân viên
                </a>
            </div>

            <div class="wg-table table-all-user">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Ngày sinh</th>
                                <th>Vị trí</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $staffMember)
                                <tr>
                                    <td>{{ ($staff->currentPage() - 1) * $staff->perPage() + $loop->iteration }}</td>
                                    <td>{{ $staffMember->name }}</td>
                                    <td>{{ $staffMember->email }}</td>
                                    <td>{{ $staffMember->mobile }}</td>
                                    <td>{{ optional($staffMember->birth_date)->format('d/m/Y') ?: '-' }}</td>
                                    <td>{{ $staffMember->position ?: 'Nhân viên' }}</td>
                                    <td>{{ optional($staffMember->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="list-icon-function">
                                            <a href="{{ route('admin.staff.edit', ['id' => $staffMember->id]) }}">
                                                <div class="item edit"><i class="icon-edit-3"></i></div>
                                            </a>
                                            <form action="{{ route('admin.staff.delete', ['id' => $staffMember->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="item text-danger" type="submit" onclick="return confirm('Xóa nhân viên này?')">
                                                    <i class="icon-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">Chưa có nhân viên.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="divider"></div>
            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $staff->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
