@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Slides</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li>
                    <div class="text-tiny">Slides</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div>
                    <h5 class="mb-1">Quản lý banner</h5>
                    <p class="text-tiny text-muted">Chia riêng banner trang chủ và banner mua sắm.</p>
                </div>
                <a class="tf-button style-1 w208" href="{{ route('admin.slide.add') }}">
                    <i class="icon-plus"></i>
                    Thêm slide mới
                </a>
            </div>

            @if(session()->has('status'))
                <p class="alert alert-success mt-3">{{ session('status') }}</p>
            @endif

            @php
                $groups = [
                    'home' => [
                        'title' => 'Banner trang chủ',
                        'slides' => $homeSlides,
                        'empty' => 'Chưa có banner trang chủ.',
                    ],
                    'shop' => [
                        'title' => 'Banner mua sắm',
                        'slides' => $shopSlides,
                        'empty' => 'Chưa có banner mua sắm.',
                    ],
                ];
            @endphp

            @foreach($groups as $placement => $group)
                <div class="divider"></div>
                <div class="flex items-center justify-between gap10 flex-wrap mb-3">
                    <h5>{{ $group['title'] }}</h5>
                    <span class="badge bg-secondary">{{ $group['slides']->count() }} banner</span>
                </div>

                <div class="wg-table table-all-user">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th width="60">#</th>
                                    <th width="130">Hình ảnh</th>
                                    <th width="140">Vị trí</th>
                                    <th>Tagline</th>
                                    <th>Tiêu đề</th>
                                    <th>Phụ đề</th>
                                    <th>Liên kết</th>
                                    <th width="110">Trạng thái</th>
                                    <th width="120">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($group['slides'] as $slide)
                                    <tr>
                                        <td>{{ $slide->id }}</td>
                                        <td class="pname">
                                            <div class="image">
                                                <img
                                                    src="{{ asset('uploads/slides/' . $slide->image) }}"
                                                    alt="{{ $slide->title }}"
                                                    style="width: 110px; height: 64px; object-fit: cover; border-radius: 8px;"
                                                    loading="lazy"
                                                >
                                            </div>
                                        </td>
                                        <td>{{ $slide->placement === 'shop' ? 'Mua sắm' : 'Trang chủ' }}</td>
                                        <td>{{ $slide->tagline }}</td>
                                        <td><strong>{{ $slide->title }}</strong></td>
                                        <td>{{ $slide->subtitle }}</td>
                                        <td>
                                            @if($slide->link)
                                                <a href="{{ $slide->link }}" target="_blank" class="text-primary">
                                                    {{ Str::limit($slide->link, 35) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $slide->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $slide->status ? 'Hoạt động' : 'Ẩn' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="list-icon-function">
                                                <a href="{{ route('admin.slide.edit', ['id' => $slide->id]) }}">
                                                    <div class="item edit">
                                                        <i class="icon-edit-3"></i>
                                                    </div>
                                                </a>
                                                <form action="{{ route('admin.slide.delete', ['id' => $slide->id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="item text-danger delete">
                                                        <i class="icon-trash-2"></i>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            {{ $group['empty'] }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.delete').on('click', function (e) {
            e.preventDefault();

            const form = $(this).closest('form');

            swal({
                title: "Bạn có chắc không?",
                text: "Một khi đã xóa, bạn sẽ không thể khôi phục lại slide này!",
                type: "warning",
                buttons: ["Không", "Có"],
                confirmButtonColor: '#3085d6',
            }).then(function (result) {
                if (result) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
