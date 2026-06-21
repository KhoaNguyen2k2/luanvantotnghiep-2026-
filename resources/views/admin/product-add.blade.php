```php
@extends('layouts.admin')
@section('content')

<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <div>
                <h3>Thêm sản phẩm</h3>
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
                        <a href="{{ route('admin.products') }}">
                            <div class="text-tiny">Products</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Thêm sản phẩm</div>
                    </li>
                </ul>
            </div>
            <a class="tf-button style-1 w208" href="{{ route('admin.products') }}">
                <i class="icon-list"></i> Xem sản phẩm đã thêm
            </a>
        </div>

        <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data" action="{{ route('admin.product.store') }}">
            @csrf

            <div class="wg-box">
                <fieldset class="name">
                    <div class="body-title mb-10">Tên sản phẩm <span class="tf-color-1">*</span></div>
                    <input class="mb-10" type="text" placeholder="Nhập tên sản phẩm" name="name" tabindex="0" value="{{ old('name') }}" aria-required="true" required>
                    <div class="text-tiny">Không vượt quá 100 ký tự.</div>
                </fieldset>
                @error('name')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <fieldset class="name">
                    <div class="body-title mb-10">Slug <span class="tf-color-1">*</span></div>
                    <input class="mb-10" type="text" placeholder="Nhập slug sản phẩm" name="slug" tabindex="0" value="{{ old('slug') }}" aria-required="true" required>
                    <div class="text-tiny">Không vượt quá 100 ký tự.</div>
                </fieldset>
                @error('slug')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <div class="gap22 cols">
                    <fieldset class="category">
                        <div class="body-title mb-10">Danh mục <span class="tf-color-1">*</span></div>
                        <div class="select">
                            <select name="category_id">
                                <option value="" selected disabled>Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('category_id')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="brand">
                        <div class="body-title mb-10">Thương hiệu <span class="tf-color-1">*</span></div>
                        <div class="select">
                            <select name="brand_id">
                                <option value="" selected disabled>Chọn thương hiệu</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>
                    @error('brand_id')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <fieldset class="shortdescription">
                    <div class="body-title mb-10">Mô tả ngắn <span class="tf-color-1">*</span></div>
                    <textarea class="mb-10 ht-150" name="short_description" placeholder="Mô tả ngắn" tabindex="0" aria-required="true" required>{{ old('short_description') }}</textarea>
                    <div class="text-tiny">Mô tả ngắn gọn sản phẩm.</div>
                </fieldset>
                @error('short_description')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <fieldset class="description">
                    <div class="body-title mb-10">Mô tả chi tiết <span class="tf-color-1">*</span></div>
                    <textarea class="mb-10" name="description" placeholder="Mô tả chi tiết" tabindex="0" aria-required="true" required>{{ old('description') }}</textarea>
                    <div class="text-tiny">Thông tin chi tiết sản phẩm.</div>
                </fieldset>
                @error('description')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror
            </div>

            <div class="wg-box">
                <fieldset>
                    <div class="body-title">Ảnh đại diện <span class="tf-color-1">*</span></div>
                    <div class="upload-image flex-grow">
                        <div class="item" id="imgpreview" style="display:none">
                            <img src="#" class="effect8" alt="">
                        </div>

                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile" for="myFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="body-text">
                                    Kéo thả ảnh hoặc <span class="tf-color">chọn ảnh</span>
                                </span>
                                <input type="file" id="myFile" name="image" accept="image/*">
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('image')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <fieldset>
                    <div class="body-title mb-10">Ảnh thư viện</div>

                    <div class="upload-image mb-16">
                        <div id="galUpload" class="item up-load">
                            <label class="uploadfile" for="gFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>
                                <span class="text-tiny">
                                    Kéo thả ảnh hoặc <span class="tf-color">chọn ảnh</span>
                                </span>
                                <input type="file" id="gFile" name="images[]" accept="image/*" multiple>
                            </label>
                        </div>
                    </div>
                </fieldset>
                @error('images')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">Giá gốc <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Nhập giá gốc" name="regular_price" tabindex="0" value="{{ old('regular_price') }}" aria-required="true" required>
                    </fieldset>
                    @error('regular_price')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">Giá khuyến mãi <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Nhập giá khuyến mãi" name="sale_price" tabindex="0" value="{{ old('sale_price') }}" aria-required="true" required>
                    </fieldset>
                    @error('sale_price')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">SKU <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Nhập SKU" name="SKU" tabindex="0" value="{{ old('SKU') }}" aria-required="true" required>
                    </fieldset>
                    @error('SKU')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">Số lượng <span class="tf-color-1">*</span></div>
                        <input class="mb-10" type="text" placeholder="Nhập số lượng" name="quantity" tabindex="0" value="{{ old('quantity') }}" aria-required="true" required>
                    </fieldset>
                    @error('quantity')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">Tình trạng kho</div>
                        <div class="select mb-10">
                            <select name="stock_status">
                                <option value="in_stock" {{ old('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
                                <option value="out_of_stock" {{ old('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('stock_status')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">Sản phẩm nổi bật</div>
                        <div class="select mb-10">
                            <select name="featured">
                                <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>Không</option>
                                <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>Có</option>
                            </select>
                        </div>
                    </fieldset>
                    @error('featured')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap10">
                    <button class="tf-button w-full" type="submit">Thêm sản phẩm</button>
                </div>
            </div>
        </form>
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

        $('#gFile').on('change', function () {
            const gphotos = this.files;

            $('.gitems').remove();

            if (!gphotos || gphotos.length === 0) {
                return;
            }

            $.each(gphotos, function (key, val) {
                $('#galUpload').prepend(
                    '<div class="item gitems"><img src="' + URL.createObjectURL(val) + '" alt=""></div>'
                );
            });
        });

        $("input[name='name']").on("change", function () {
            $("input[name='slug']").val(stringToSlug($(this).val()));
        });
    });

    function stringToSlug(text)
    {
        return text.toLowerCase()
            .replace(/[^\w]+/g, "-")
            .replace(/ +/g, "-")
            .replace(/^-+|-+$/g, "");
    }
</script>
@endpush
```
