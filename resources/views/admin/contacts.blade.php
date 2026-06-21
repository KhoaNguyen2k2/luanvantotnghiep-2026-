```php
@extends('layouts.admin')
@section('content') 

<div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                                    <h3>Tất cả tin nhắn</h3>
                                    <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                                        <li>
                                            <a href="{{ route('admin.index') }}">
                                                <div class="text-tiny">Bảng điều khiển</div>
                                            </a>
                                        </li>
                                        <li>
                                            <i class="icon-chevron-right"></i>
                                        </li>
                                        <li>
                                            <div class="text-tiny">Mã giảm giá</div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="wg-box">
                                    <div class="flex items-center justify-between gap10 flex-wrap">
                                        <div class="wg-filter flex-grow">
                                            <form class="form-search">
                                                <fieldset class="name">
                                                    <input type="text" placeholder="Tìm kiếm tại đây..." class="" name="name"
                                                        tabindex="2" value="" aria-required="true" required="">
                                                </fieldset>
                                                <div class="button-submit">
                                                    <button class="" type="submit"><i class="icon-search"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                        <a class="tf-button style-1 w208" href="{{ route('admin.coupon.add') }}"><i class="icon-plus"></i>Thêm mới</a>
                                    </div>
                                    <div class="wg-table table-all-user">
                                        <div class="table-responsive">
                                             @if(session()->has('status'))
                                                <p class="alert alert-success">{{ session('status') }}</p>
                                            @endif
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tên</th>
                                                        <th>Số điện thoại</th>
                                                        <th>Email</th>
                                                        <th>Tin nhắn</th>
                                                        <th>Ngày</th>
                                                        <th>Hành động</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($contacts as $contact)
                                                    <tr>
                                                        <td>{{ $contact->id }}</td>
                                                        <td>{{ $contact->name }}</td>
                                                        <td>{{ $contact->phone }}</td>
                                                        <td>{{ $contact->email }}</td>
                                                        <td>{{ $contact->comment }}</td>
                                                        <td>{{ $contact->created_at }}</td>
                                                        <td>
                                                            <div class="list-icon-function">
                                                                
                                                                <form action="{{ route('admin.contact.delete',['id'=>$contact->id]) }}" method="POST">
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
                                    </div>
                                    <div class="divider"></div>
                                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                                        {{ $contacts->links('pagination::bootstrap-5') }}

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
                    text: "Một khi đã xóa, bạn sẽ không thể khôi phục lại tin nhắn này!",
                    type: "warning",
                    buttons:["KHÔNG", "CÓ"],
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

