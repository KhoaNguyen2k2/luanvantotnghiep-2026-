@extends('layouts.admin')
@push('styles')
<style>
    .admin-products-page .flex.mb-27 {
        margin-bottom: 18px;
    }

    .admin-products-page .wg-box {
        gap: 14px;
        padding: 18px;
    }

    .admin-products-page .table-responsive {
        overflow-x: auto;
    }

    .admin-products-page .table {
        table-layout: fixed;
        margin-bottom: 0;
        min-width: 1180px;
    }

    .admin-products-page .table-bordered > :not(caption) > * > * {
        padding: 6px 8px;
        line-height: 1.25;
        font-size: 12px;
        vertical-align: middle;
    }

    .admin-products-page .table th:nth-child(1),
    .admin-products-page .table td:nth-child(1) {
        width: 46px;
    }

    .admin-products-page .table th:nth-child(2),
    .admin-products-page .table td:nth-child(2) {
        width: 220px;
        padding-bottom: 6px;
    }

    .admin-products-page .table th:nth-child(11),
    .admin-products-page .table td:nth-child(11) {
        width: 116px;
    }

    .admin-products-page .pname {
        align-items: center;
        gap: 8px;
    }

    .admin-products-page .pname > .image {
        width: 38px;
        height: 38px;
        border-radius: 8px;
    }

    .admin-products-page .pname img.image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-products-page .pname .name {
        min-width: 0;
    }

    .admin-products-page .pname .body-title-2 {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-height: 1.18;
    }

    .admin-products-page .pname .text-tiny {
        margin-top: 2px !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.15;
    }

    .admin-products-page .divider {
        margin: 0;
    }

    .admin-products-page .wgp-pagination {
        padding-top: 4px;
    }

    .admin-products-page .wgp-pagination p {
        margin-bottom: 0;
        font-size: 13px;
    }

    .admin-products-page .wgp-pagination .pagination {
        margin: 0 0 0 14px;
    }

    .admin-products-page .wgp-pagination .page-link {
        min-width: 30px;
        height: 30px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        line-height: 1;
    }
</style>
@endpush
@section('content')

<div class="main-content-inner admin-products-page">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Tất cả sản phẩm</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}" class="">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Sản phẩm</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <form class="form-search" method="GET" action="{{ route('admin.products') }}">
                        <fieldset class="name">
                            <input type="search" placeholder="Tìm theo tên / SKU / slug..." class="" name="q"
                                tabindex="2" value="{{ $searchQ ?? '' }}" autocomplete="off">
                        </fieldset>
                        <div class="button-submit">
                            <button class="" type="submit"><i class="icon-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="tb-summary text-tiny">
                    Tổng sản phẩm: {{ $products->total() }}
                </div>
                <a class="tf-button style-1 w208" href="{{ route('admin.product.add') }}">
                    <i class="icon-plus"></i>Thêm sản phẩm mới
                </a>
            </div>

            <div class="table-responsive">
                @if(Session::has('status'))
                    <p class="alert alert-success">{{ Session::get('status') }}</p>
                @endif

                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá gốc</th>
                            <th>Giá sale</th>
                            <th>SKU</th>
                            <th>Danh mục</th>
                            <th>Thương hiệu</th>
                            <th>Nổi bật</th>
                            <th>Tồn kho</th>
                            <th>Số lượng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $product)
                        <tr>
                            <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>

                            <td class="pname">
                                <div class="image">
                                    @php
                                        $thumbPath = public_path('uploads/products/thumbnails/' . ($product->image ?? ''));
                                        $imgSrc = (!empty($product->image) && file_exists($thumbPath))
                                            ? asset('uploads/products/thumbnails/' . $product->image)
                                            : asset('uploads/products/' . ($product->image ?: ''));
                                    @endphp

                                    <img src="{{ $imgSrc }}" alt="{{ $product->name }}" class="image">
                                </div>

                                <div class="name">
                                    <a href="#" class="body-title-2">{{ $product->name }}</a>
                                    <div class="text-tiny mt-3">{{ $product->slug }}</div>
                                </div>
                            </td>

                            <td>{{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫</td>

                            <td>{{ number_format((float) $product->sale_price, 0, ',', '.') }} ₫</td>

                            <td>{{ $product->SKU }}</td>

                            <td>{{ $product->category->name }}</td>

                            <td>{{ $product->brand->name }}</td>

                            <td>{{ (int) $product->featured === 1 ? 'CÓ' : 'KHÔNG' }}</td>

                            <td>{{ $product->stock_status }}</td>

                            <td>{{ $product->quantity }}</td>

                            <td>
                                <div class="list-icon-function">
                                    <a href="#" target="_blank">
                                        <div class="item eye">
                                            <i class="icon-eye"></i>
                                        </div>
                                    </a>

                                    <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}">
                                        <div class="item edit">
                                            <i class="icon-edit-3"></i>
                                        </div>
                                    </a>

                                    <form action="{{ route('admin.product.delete', ['id' => $product->id]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <div class="item text-danger delete">
                                            <i class="icon-trash-2"></i>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>

            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {
        $('.delete').on('click', function(e) {
            e.preventDefault();

            var form = $(this).closest('form');

            swal({
                title: "Bạn có chắc không?",
                text: "Một khi đã xóa, bạn sẽ không thể khôi phục lại sản phẩm này!",
                type: "warning",
                buttons: ["NO", "YES"],
                confirmButtonColor: '#3085d6',
            }).then(function(result) {
                if (result) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
