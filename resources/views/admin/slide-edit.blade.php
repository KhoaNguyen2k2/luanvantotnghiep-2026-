@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Chỉnh sửa slide</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li>
                    <a href="{{ route('admin.slides') }}">
                        <div class="text-tiny">Slides</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li>
                    <div class="text-tiny">Chỉnh sửa slide</div>
                </li>
            </ul>
        </div>

        <div class="wg-box">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="form-new-product form-style-1" action="{{ route('admin.slide.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $slide->id }}">

                <fieldset class="category">
                    <div class="body-title">Hiển thị ở <span class="tf-color-1">*</span></div>
                    <div class="select flex-grow">
                        <select name="placement" required>
                            <option value="">Chọn nơi hiển thị</option>
                            <option value="home" {{ old('placement', $slide->placement ?? 'home') === 'home' ? 'selected' : '' }}>Banner trang chủ</option>
                            <option value="shop" {{ old('placement', $slide->placement ?? 'home') === 'shop' ? 'selected' : '' }}>Banner mua sắm</option>
                        </select>
                    </div>
                    @error('placement')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Tagline <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Nhập tagline" name="tagline" value="{{ old('tagline', $slide->tagline) }}" required>
                    @error('tagline')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Tiêu đề <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Nhập tiêu đề" name="title" value="{{ old('title', $slide->title) }}" required>
                    @error('title')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Phụ đề <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Nhập phụ đề" name="subtitle" value="{{ old('subtitle', $slide->subtitle) }}" required>
                    @error('subtitle')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="name">
                    <div class="body-title">Liên kết <span class="tf-color-1">*</span></div>
                    <input class="flex-grow" type="text" placeholder="Nhập liên kết" name="link" value="{{ old('link', $slide->link) }}" required>
                    @error('link')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset>
                    <div class="body-title">Hình ảnh</div>
                    <div class="upload-image flex-grow">
                        @if($slide->image)
                            <div class="item" id="imgpreview">
                                <img src="{{ asset('uploads/slides/' . $slide->image) }}" class="effect8" alt="{{ $slide->title }}">
                            </div>
                        @else
                            <div class="item" id="imgpreview" style="display:none">
                                <img src="" class="effect8" alt="">
                            </div>
                        @endif
                        <div class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon"><i class="icon-upload-cloud"></i></span>
                                <span class="body-text">
                                    Kéo thả ảnh vào đây hoặc <span class="tf-color">bấm để chọn ảnh</span>
                                </span>
                                <input type="file" id="myFile" name="image" accept="image/*">
                            </label>
                        </div>
                    </div>
                    @error('image')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset class="category">
                    <div class="body-title">Trạng thái</div>
                    <div class="select flex-grow">
                        <select name="status" required>
                            <option value="">Chọn trạng thái</option>
                            <option value="1" {{ old('status', $slide->status) == "1" ? 'selected' : '' }}>Hoạt động</option>
                            <option value="0" {{ old('status', $slide->status) == "0" ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    @error('status')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
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

@push('scripts')
<script>
    $(function () {
        $('#myFile').on('change', function () {
            const [file] = this.files;
            if (file) {
                $('#imgpreview img').attr('src', URL.createObjectURL(file));
                $('#imgpreview').show();
            }
        });
    });
</script>
@endpush
