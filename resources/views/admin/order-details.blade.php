```blade
@extends('layouts.admin')
@section('content')

<style>
.table-transaction>tbody>tr:nth-of-type(odd){
    --bs-table-accent-bg:#fff!important;
}
.admin-order-items .pname{
    min-width:260px;
}
.admin-order-items .image img{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid #eee;
}
.order-item-thumb-fallback{
    width:60px;
    height:60px;
    display:inline-block;
    background:#f3f3f3;
    border-radius:8px;
    border:1px dashed #ccc;
}
.admin-order-cell-wrap{
    white-space:normal;
    word-break:break-word;
    max-width:180px;
}
.admin-order-cell-options{
    max-width:220px;
}
.order-item-name-link{
    line-height:1.4;
}
.order-summary-table th{
    width:180px;
    background:#fafafa;
}
.shipping-box p{
    margin-bottom:6px;
}
</style>

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Order Details</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <a href="{{ route('admin.orders') }}">
                        <div class="text-tiny">Orders</div>
                    </a>
                </li>
                <li>
                    <i class="icon-chevron-right"></i>
                </li>
                <li>
                    <div class="text-tiny">Order Details</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <h5>Thông tin đơn hàng</h5>
                </div>
                <a class="tf-button style-1 w208" href="{{ route('admin.orders') }}">Quay lại</a>
            </div>

            @if(Session::has('status'))
                <p class="alert alert-success mt-3">{{ Session::get('status') }}</p>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered order-summary-table">
                    <tr>
                        <th>Mã đơn hàng</th>
                        <td>#{{ $order->id }}</td>
                        <th>Khách hàng</th>
                        <td>{{ $order->name }}</td>
                    </tr>

                    <tr>
                        <th>Số điện thoại</th>
                        <td>{{ $order->phone }}</td>
                        <th>Mã bưu điện</th>
                        <td>{{ $order->zip }}</td>
                    </tr>

                    <tr>
                        <th>Ngày đặt</th>
                        <td>{{ $order->created_at }}</td>
                        <th>Ngày giao</th>
                        <td>{{ $order->delivered_date ?? '—' }}</td>
                    </tr>

                    <tr>
                        <th>Ngày hủy</th>
                        <td>{{ $order->canceled_date ?? '—' }}</td>
                        <th>Trạng thái</th>
                        <td>
                            @if($order->status == 'delivered')
                                <span class="badge bg-success">Delivered</span>
                            @elseif($order->status == 'canceled')
                                <span class="badge bg-danger">Canceled</span>
                            @else
                                <span class="badge bg-warning">Ordered</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Tạm tính</th>
                        <td>{{ number_format((float)$order->subtotal,0,',','.') }} ₫</td>
                        <th>Thuế</th>
                        <td>{{ number_format((float)$order->tax,0,',','.') }} ₫</td>
                    </tr>

                    <tr>
                        <th>Tổng cộng</th>
                        <td>{{ number_format((float)$order->total,0,',','.') }} ₫</td>
                        <th>Tổng sản phẩm</th>
                        <td>{{ $order->orderItems->sum('quantity') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="wg-box mt-5">
            <div class="flex items-center justify-between gap10 flex-wrap">
                <div class="wg-filter flex-grow">
                    <h5>Danh sách sản phẩm</h5>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered admin-order-items">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-center">SKU</th>
                            <th class="text-center">Danh mục</th>
                            <th class="text-center">Thương hiệu</th>
                            <th class="text-center">Tùy chọn</th>
                            <th class="text-center">Đổi trả</th>
                            <th class="text-center">Xem</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($orderItems as $item)
                        @php
                            $product = $item->product;
                            $itemImgSrc = null;

                            if ($product) {
                                $pImg = $product->image ?? '';
                                $thumbPath = public_path('uploads/products/thumbnails/'.$pImg);
                                $fullPath = public_path('uploads/products/'.$pImg);

                                if ($pImg !== '' && file_exists($thumbPath)) {
                                    $itemImgSrc = asset('uploads/products/thumbnails/'.$pImg);
                                } elseif ($pImg !== '' && file_exists($fullPath)) {
                                    $itemImgSrc = asset('uploads/products/'.$pImg);
                                }
                            }
                        @endphp

                        <tr>
                            <td class="pname">
                                <div class="image">
                                    @if ($itemImgSrc)
                                        <img src="{{ $itemImgSrc }}" alt="{{ $product?->name ?? 'Sản phẩm' }}" loading="lazy">
                                    @else
                                        <span class="order-item-thumb-fallback"></span>
                                    @endif
                                </div>

                                <div class="name">
                                    @if ($product)
                                        <a href="{{ route('shop.product.details',['product_slug'=>$product->slug]) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="body-title-2 order-item-name-link">
                                            {{ $product->name }}
                                        </a>

                                        <div class="text-tiny mt-2">
                                            {{ $product->slug }}
                                        </div>
                                    @else
                                        <span class="body-title-2 text-muted">
                                            (Sản phẩm đã bị xóa)
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center text-nowrap">
                                {{ number_format((float)$item->price,0,',','.') }} ₫
                            </td>

                            <td class="text-center">
                                {{ $item->quantity }}
                            </td>

                            <td class="text-center admin-order-cell-wrap">
                                {{ $product?->SKU ?? '—' }}
                            </td>

                            <td class="text-center admin-order-cell-wrap">
                                {{ $product?->category?->name ?? '—' }}
                            </td>

                            <td class="text-center admin-order-cell-wrap">
                                {{ $product?->brand?->name ?? '—' }}
                            </td>

                            <td class="text-center admin-order-cell-wrap admin-order-cell-options">
                                {{ $item->options ?? '—' }}
                            </td>

                            <td class="text-center">
                                @if($item->rstatus == 1)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($product)
                                <a href="{{ route('shop.product.details',['product_slug'=>$product->slug]) }}"
                                   target="_blank">
                                    <div class="list-icon-function view-icon">
                                        <div class="item eye">
                                            <i class="icon-eye"></i>
                                        </div>
                                    </div>
                                </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>

            <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                {{ $orderItems->links('pagination::bootstrap-5') }}
            </div>
        </div>

        <div class="wg-box mt-5">
            <h5>Địa chỉ giao hàng</h5>

            <div class="my-account__address-item col-md-6 shipping-box">
                <div class="my-account__address-item__detail">
                    <p><strong>{{ $order->name }}</strong></p>
                    <p>{{ $order->address }}</p>
                    <p>{{ $order->locality }}</p>
                    <p>{{ $order->city }}, {{ $order->state }}, {{ $order->country }}</p>

                    @if($order->landmark)
                        <p>Mốc địa chỉ: {{ $order->landmark }}</p>
                    @endif

                    <p>{{ $order->zip }}</p>

                    <br>

                    <p>
                        <strong>Mobile:</strong> {{ $order->phone }}
                    </p>
                </div>
            </div>
        </div>

        <div class="wg-box mt-5">
            <h5>Cập nhật trạng thái đơn hàng</h5>

            <form action="{{ route('admin.order.update_status',['order_id'=>$order->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="order_id" value="{{ $order->id }}"/>

                <div class="row">
                    <div class="col-md-3">
                        <div class="select">
                            <select id="order_status" name="status">
                                <option value="ordered" {{ $order->status == 'ordered' ? 'selected' : '' }}>
                                    Ordered
                                </option>

                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>
                                    Delivered
                                </option>

                                <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>
                                    Canceled
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary tf-button w208">
                            Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
```
