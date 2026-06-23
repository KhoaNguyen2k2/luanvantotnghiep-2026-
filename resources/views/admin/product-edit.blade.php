@extends('layouts.admin')
@push('styles')
<style>
    .admin-product-edit-page .flex.mb-27 {
        margin-bottom: 16px;
    }

    .admin-product-edit-page .tf-section-2 {
        gap: 16px;
        align-items: start;
    }

    .admin-product-edit-page .wg-box {
        gap: 14px;
        padding: 18px 20px;
    }

    .admin-product-edit-page .body-title.mb-10 {
        margin-bottom: 6px;
    }

    .admin-product-edit-page input.mb-10,
    .admin-product-edit-page textarea.mb-10,
    .admin-product-edit-page .select.mb-10 {
        margin-bottom: 5px !important;
    }

    .admin-product-edit-page form input[type=text],
    .admin-product-edit-page form textarea {
        padding: 11px 16px;
        border-radius: 10px;
    }

    .admin-product-edit-page form textarea {
        height: 148px !important;
    }

    .admin-product-edit-page textarea.ht-150 {
        height: 126px !important;
    }

    .admin-product-edit-page .cols {
        gap: 14px;
    }

    .admin-product-edit-page .upload-image {
        gap: 8px;
    }

    .admin-product-edit-page .upload-image .item.up-load {
        min-height: 132px;
        overflow: hidden;
    }

    .admin-product-edit-page .upload-image .uploadfile {
        min-height: 132px;
        padding: 12px;
    }

    .admin-product-edit-page .upload-image .uploadfile .icon {
        font-size: 32px;
    }

    .admin-product-edit-page .upload-image .uploadfile .body-text,
    .admin-product-edit-page .upload-image .uploadfile .text-tiny {
        font-size: 12px;
        line-height: 1.35;
    }

    .admin-product-edit-page .product-main-upload .uploadfile.has-preview .icon,
    .admin-product-edit-page .product-main-upload .uploadfile.has-preview .body-text {
        display: none;
    }

    .admin-product-edit-page .product-main-upload__preview {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 132px;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .admin-product-edit-page .product-main-upload__preview img {
        max-width: 100%;
        max-height: 116px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
    }

    .admin-product-edit-page .product-main-upload .uploadfile.has-preview .product-main-upload__preview {
        display: flex;
    }

    .admin-product-edit-page .gallery-upload .gallery-existing,
    .admin-product-edit-page .gallery-upload .gitems {
        width: 92px;
        height: 92px;
        overflow: hidden;
    }

    .admin-product-edit-page .gallery-upload .gallery-existing img,
    .admin-product-edit-page .gallery-upload .gitems img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .admin-product-edit-page .tf-button.w-full {
        padding-top: 12px;
        padding-bottom: 12px;
    }

    @media (max-width: 1199.98px) {
        .admin-product-edit-page .tf-section-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
@section('content')

<div class="main-content-inner admin-product-edit-page">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <div>
                <h3>Chỉnh sửa sản phẩm</h3>

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
                            <div class="text-tiny">Sản phẩm</div>
                        </a>
                    </li>

                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>

                    <li>
                        <div class="text-tiny">Chỉnh sửa sản phẩm</div>
                    </li>
                </ul>
            </div>
            <a class="tf-button style-1 w208" href="{{ route('admin.products') }}">
                <i class="icon-list"></i> Xem sản phẩm đã thêm
            </a>
        </div>

        <form class="tf-section-2 form-add-product" method="POST" enctype="multipart/form-data" action="{{ route('admin.product.update', ['id' => $product->id]) }}">
            @csrf
            @method('PUT')

            <div class="wg-box">
                <fieldset class="name">
                    <div class="body-title mb-10">
                        Tên sản phẩm <span class="tf-color-1">*</span>
                    </div>

                    <input class="mb-10" type="text" placeholder="Nhập tên sản phẩm" name="name" tabindex="0" value="{{ $product->name }}" aria-required="true" required="">

                    <div class="text-tiny">
                        Không vượt quá 100 ký tự khi nhập tên sản phẩm.
                    </div>
                </fieldset>

                @error('name')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <fieldset class="name">
                    <div class="body-title mb-10">
                        Slug <span class="tf-color-1">*</span>
                    </div>

                    <input class="mb-10" type="text" placeholder="Nhập slug sản phẩm" name="slug" tabindex="0" value="{{ $product->slug }}" aria-required="true" required="">

                    <div class="text-tiny">
                        Không vượt quá 100 ký tự khi nhập slug sản phẩm.
                    </div>
                </fieldset>

                @error('slug')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <div class="gap22 cols">
                    <fieldset class="category">
                        <div class="body-title mb-10">
                            Danh mục <span class="tf-color-1">*</span>
                        </div>

                        <div class="select">
                            <select class="" name="category_id">
                                <option value="" selected disabled>Chọn danh mục</option>

                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? "selected" : "" }}>
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
                        <div class="body-title mb-10">
                            Thương hiệu <span class="tf-color-1">*</span>
                        </div>

                        <div class="select">
                            <select class="" name="brand_id">
                                <option value="" selected disabled>Chọn thương hiệu</option>

                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? "selected" : "" }}>
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
                    <div class="body-title mb-10">
                        Mô tả ngắn <span class="tf-color-1">*</span>
                    </div>

                    <textarea class="mb-10 ht-150" name="short_description" placeholder="Mô tả ngắn" tabindex="0" aria-required="true" required=" ">{{ $product->short_description }}</textarea>

                    <div class="text-tiny">
                        Không vượt quá 100 ký tự khi nhập mô tả ngắn.
                    </div>
                </fieldset>

                @error('short_description')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <fieldset class="description">
                    <div class="body-title mb-10">
                        Mô tả chi tiết <span class="tf-color-1">*</span>
                    </div>

                    <textarea class="mb-10" name="description" placeholder="Mô tả chi tiết" tabindex="0" aria-required="true" required="">{{ $product->description }}</textarea>

                    <div class="text-tiny">
                        Không vượt quá 100 ký tự khi nhập mô tả sản phẩm.
                    </div>
                </fieldset>

                @error('description')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror
            </div>

            <div class="wg-box">
                <fieldset>
                    <div class="body-title">
                        Tải ảnh sản phẩm <span class="tf-color-1">*</span>
                    </div>

                    <div class="upload-image flex-grow product-main-upload">
                        <div id="upload-file" class="item up-load">
                            <label class="uploadfile {{ $product->image ? 'has-preview' : '' }}" for="myFile">
                                <span class="product-main-upload__preview" id="imgpreview">
                                    <img src="{{ $product->image ? asset('uploads/products/' . $product->image) : '#' }}" class="effect8" alt="{{ $product->name }}">
                                </span>

                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>

                                <span class="body-text">
                                    Kéo ảnh vào đây hoặc
                                    <span class="tf-color">bấm để chọn ảnh</span>
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
                    <div class="body-title mb-10">
                        Tải thư viện ảnh
                    </div>

                    <div class="upload-image mb-16 gallery-upload">
                        @if($product->images)
                            @foreach(explode(',', $product->images) as $img)
                                @php($img = trim($img))

                                @if($img !== '')
                                    <div class="item gallery-existing" style="position:relative">
                                        <img src="{{ asset('uploads/products/' . $img) }}" alt="">

                                        <label style="position:absolute;top:6px;right:6px;background:#fff;padding:4px 6px;border-radius:6px;display:flex;gap:6px;align-items:center;font-size:12px;">
                                            <input type="checkbox" name="remove_images[]" value="{{ $img }}">
                                            Xóa
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <div id="galUpload" class="item up-load">
                            <label class="uploadfile" for="gFile">
                                <span class="icon">
                                    <i class="icon-upload-cloud"></i>
                                </span>

                                <span class="text-tiny">
                                    Kéo ảnh vào đây hoặc
                                    <span class="tf-color">bấm để chọn ảnh</span>
                                </span>

                                <input type="file" id="gFile" name="images[]" accept="image/*" multiple="">
                            </label>
                        </div>
                    </div>
                </fieldset>

                @error('images')
                    <span class="alert alert-danger text-center">{{ $message }}</span>
                @enderror

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">
                            Giá gốc <span class="tf-color-1">*</span>
                        </div>

                        <input class="mb-10" type="text" placeholder="Nhập giá gốc" name="regular_price" tabindex="0" value="{{ number_format((float) $product->regular_price, 0, ',', '.') }}" aria-required="true" required="">
                    </fieldset>

                    @error('regular_price')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">
                            Giá khuyến mãi <span class="tf-color-1">*</span>
                        </div>

                        <input class="mb-10" type="text" placeholder="Nhập giá khuyến mãi" name="sale_price" tabindex="0" value="{{ number_format((float) $product->sale_price, 0, ',', '.') }}" aria-required="true" required="">
                    </fieldset>

                    @error('sale_price')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">
                            SKU <span class="tf-color-1">*</span>
                        </div>

                        <input class="mb-10" type="text" placeholder="Nhập SKU" name="SKU" tabindex="0" value="{{ $product->SKU }}" aria-required="true" required="">
                    </fieldset>

                    @error('SKU')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">
                            Số lượng <span class="tf-color-1">*</span>
                        </div>

                        <input class="mb-10" type="text" placeholder="Nhập số lượng" name="quantity" tabindex="0" value="{{ $product->quantity }}" aria-required="true" required="">
                    </fieldset>

                    @error('quantity')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap22">
                    <fieldset class="name">
                        <div class="body-title mb-10">
                            Tồn kho
                        </div>

                        <div class="select mb-10">
                            <select class="" name="stock_status">
                                <option value="in_stock" {{ $product->stock_status == "in_stock" ? "selected" : "" }}>
                                    Còn hàng
                                </option>

                                <option value="out_of_stock" {{ $product->stock_status == "out_of_stock" ? "selected" : "" }}>
                                    Hết hàng
                                </option>
                            </select>
                        </div>
                    </fieldset>

                    @error('stock_status')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror

                    <fieldset class="name">
                        <div class="body-title mb-10">
                            Sản phẩm nổi bật
                        </div>

                        <div class="select mb-10">
                            <select class="" name="featured">
                                <option value="0" {{ (int) $product->featured === 0 ? "selected" : "" }}>
                                    Không
                                </option>

                                <option value="1" {{ (int) $product->featured === 1 ? "selected" : "" }}>
                                    Có
                                </option>
                            </select>
                        </div>
                    </fieldset>

                    @error('featured')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                </div>

                <div class="cols gap10">
                    <button class="tf-button w-full" type="submit">
                        Lưu
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function () {
        $('#myFile').on('change', function(e){
            const [file] = this.files;

            if (file) {
                $('#imgpreview img').attr('src', URL.createObjectURL(file));
                $('#myFile').closest('.uploadfile').addClass('has-preview');
            }
        });

        $('#gFile').on('change', function(){
            const gphotos = this.files;

            $('.gitems--new').remove();

            if (!gphotos || gphotos.length === 0) {
                return;
            }

            $.each(gphotos, function(key, val){
                $('#galUpload').before(
                    '<div class="item gitems gitems--new"><img src="' + URL.createObjectURL(val) + '" alt=""></div>'
                );
            });
        });

        $("input[name='name']").on("change", function(){
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
