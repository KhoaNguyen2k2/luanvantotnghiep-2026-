@extends('layouts.app')
@section('content')
    <style>
      /* Tim đã có trong wishlist — cùng tông cam như trang chi tiết (tutorial) */
      .pc__btn-wl--in-wishlist {
        color: #f97316;
      }
      .pc__btn-wl--in-wishlist svg {
        color: inherit;
      }
      .pc__btn-wl--in-wishlist:disabled {
        opacity: 1;
        cursor: default;
      }

      .shop-banner-slider {
        width: 100%;
        aspect-ratio: 3 / 1;
        max-height: 320px;
        min-height: 220px;
        background: #fff;
        overflow: hidden;
      }

      .shop-banner-frame,
      .shop-banner-frame .swiper-wrapper,
      .shop-banner-frame .swiper-slide,
      .shop-banner-slide {
        height: 100%;
      }

      .shop-banner-slide {
        display: block;
        overflow: hidden;
        background: #fff;
      }

      .shop-banner-side {
        display: none !important;
      }

      .shop-banner-media {
        width: 100%;
        height: 100%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
      }

      .shop-banner-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
      }

      .shop-banner-arrow-navigation {
        left: 0;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 5;
        padding: 0 18px;
        justify-content: space-between;
        pointer-events: none;
      }

      .shop-banner-arrow {
        width: 42px;
        height: 42px;
        border: 1px solid #222;
        border-radius: 50%;
        background: rgba(255, 255, 255, .88);
        color: #222;
        font-size: 22px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
        pointer-events: auto;
      }

      .shop-banner-arrow:hover,
      .shop-banner-arrow:focus {
        background: #222;
        color: #fff;
      }

      @media (max-width: 767.98px) {
        .shop-banner-slider {
          min-height: 150px;
        }

        .shop-banner-arrow {
          width: 36px;
          height: 36px;
          font-size: 18px;
        }
      }
    </style>
<main class="pt-90">
    <section class="shop-main container d-flex pt-4 pt-xl-5">
      <div class="shop-sidebar side-sticky bg-body" id="shopFilter">
        <div class="aside-header d-flex d-lg-none align-items-center">
          <h3 class="text-uppercase fs-6 mb-0">Bộ lọc</h3>
          <button class="btn-close-lg js-close-aside btn-close-aside ms-auto"></button>
        </div>

        <div class="pt-4 pt-lg-0"></div>

        <div class="accordion" id="categories-list">
          <div class="accordion-item mb-4 pb-3">
            <h5 class="accordion-header" id="accordion-heading-1">
              <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-filter-1" aria-expanded="true" aria-controls="accordion-filter-1">
                Danh mục sản phẩm
                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                  <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                    <path
                      d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                  </g>
                </svg>
              </button>
            </h5>
            <div id="accordion-filter-1" class="accordion-collapse collapse show border-0"
              aria-labelledby="accordion-heading-1" data-bs-parent="#categories-list">
              <div class="accordion-body px-0 pb-0 pt-3 categories-list">
                <ul class="list list-inline mb-0">
                  @foreach($categories as $category)
                  
                  <li class="list-item">
                    <div class="d-flex align-items-center justify-content-between py-1">
                      <div class="form-check m-0">
                        <input
                          class="form-check-input chk-category"
                          type="checkbox"
                          name="categories"
                          id="category-{{ $category->id }}"
                          value="{{ $category->id }}"
                          @if(!empty($selectedCategoryIds ?? []) && in_array($category->id, $selectedCategoryIds)) checked @endif
                        >
                        <label class="form-check-label menu-link" for="category-{{ $category->id }}">
                          {{ $category->name }}
                        </label>
                      </div>
                      <span class="text-muted small">{{ $category->products_count }}</span>
                    </div>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        </div>


        <div class="accordion" id="color-filters">
          <div class="accordion-item mb-4 pb-3">
            <h5 class="accordion-header" id="accordion-heading-1">
              <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-filter-2" aria-expanded="true" aria-controls="accordion-filter-2">
                
                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                  <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                    <path
                      d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                  </g>
                </svg>
              </button>
            </h5>
            <div id="accordion-filter-2" class="accordion-collapse collapse show border-0"
              aria-labelledby="accordion-heading-1" data-bs-parent="#color-filters">
              <div class="accordion-body px-0 pb-0">
                <div class="d-flex flex-wrap">
                  
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- <div class="accordion" id="size-filters">
          <div class="accordion-item mb-4 pb-3">
            <div id="accordion-filter-size" class="accordion-collapse collapse show border-0"
              aria-labelledby="accordion-heading-size" data-bs-parent="#size-filters">
              <div class="accordion-body px-0 pb-0">
              </div>
            </div>
          </div>
        </div> -->


        <div class="accordion" id="brand-filters">
          <div class="accordion-item mb-4 pb-3">
            <h5 class="accordion-header" id="accordion-heading-brand">
              <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-filter-brand" aria-expanded="true" aria-controls="accordion-filter-brand">
                Thương hiệu
                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                  <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                    <path
                      d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                  </g>
                </svg>
              </button>
            </h5>
            <div id="accordion-filter-brand" class="accordion-collapse collapse show border-0"
              aria-labelledby="accordion-heading-brand" data-bs-parent="#brand-filters">
              <div class="search-field multi-select accordion-body px-0 pb-0">
                <ul class="list list-inline mb-0 brands-list">
                  @foreach ($brands as $brand)
                  <li class="list-item">
                    <div class="d-flex align-items-center justify-content-between py-1">
                      <div class="form-check m-0">
                        <input
                          class="form-check-input chk-brand"
                          type="checkbox"
                          name="brands"
                          id="brand-{{ $brand->id }}"
                          value="{{ $brand->id }}"
                          @if(!empty($selectedBrandIds ?? []) && in_array($brand->id, $selectedBrandIds)) checked @endif
                        >
                        <label class="form-check-label menu-link" for="brand-{{ $brand->id }}">
                          {{ $brand->name }}
                        </label>
                      </div>
                      <span class="text-muted small">{{ $brand->products_count }}</span>
                    </div>
                  </li>
                  @endforeach
              </div>
            </div>
          </div>
        </div>


        <div class="accordion" id="price-filters">
          <div class="accordion-item mb-4">
            <h5 class="accordion-header mb-2" id="accordion-heading-price">
              <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button" data-bs-toggle="collapse"
                data-bs-target="#accordion-filter-price" aria-expanded="true" aria-controls="accordion-filter-price">
                Giá
                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                  <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                    <path
                      d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z" />
                  </g>
                </svg>
              </button>
            </h5>
            <div id="accordion-filter-price" class="accordion-collapse collapse show border-0"
              aria-labelledby="accordion-heading-price" data-bs-parent="#price-filters">
              @php
                $priceRanges = [
                  ['label' => 'Dưới 10 triệu',     'min' => 0,         'max' => 10000000],
                  ['label' => '10 - 15 triệu',     'min' => 10000000,  'max' => 15000000],
                  ['label' => '15 - 20 triệu',     'min' => 15000000,  'max' => 20000000],
                  ['label' => '20 - 25 triệu',     'min' => 20000000,  'max' => 25000000],
                  ['label' => '25 - 30 triệu',     'min' => 25000000,  'max' => 30000000],
                  ['label' => '30 - 35 triệu',     'min' => 30000000,  'max' => 35000000],
                  ['label' => 'Trên 35 triệu',     'min' => 35000000,  'max' => 1000000000],
                ];

                $currentMin = (int)($min_price ?? 0);
                $currentMax = (int)($max_price ?? 1000000000);
              @endphp

              <div class="d-flex flex-wrap gap-2 pt-2">
                <button
                  type="button"
                  class="btn btn-sm {{ ($currentMin <= 0 && $currentMax >= 1000000000) ? 'btn-dark' : 'btn-outline-dark' }} js-price-range"
                  data-min="0"
                  data-max="1000000000"
                >
                  Tất cả
                </button>

                @foreach ($priceRanges as $r)
                  @php
                    $isActive = ($currentMin == (int)$r['min'] && $currentMax == (int)$r['max']);
                  @endphp
                  <button
                    type="button"
                    class="btn btn-sm {{ $isActive ? 'btn-dark' : 'btn-outline-dark' }} js-price-range"
                    data-min="{{ (int)$r['min'] }}"
                    data-max="{{ (int)$r['max'] }}"
                  >
                    {{ $r['label'] }}
                  </button>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="shop-list flex-grow-1">
        @php
          $shopBannerLimit = max(1, min(5, (int) ($shopBannerLimit ?? 5)));
        @endphp
        <div class="swiper-container js-swiper-slider shop-banner-slider shop-banner-frame position-relative" data-settings='{
            "autoplay": {
              "delay": 5000
            },
            "slidesPerView": 1,
            "effect": "fade",
            "loop": true,
            "navigation": {
              "nextEl": ".shop-banner-button-next",
              "prevEl": ".shop-banner-button-prev"
            }
          }'>
          <div class="swiper-wrapper">
            @forelse($shopSlides as $slide)
            <div class="swiper-slide">
              <div class="shop-banner-slide">
                <div class="shop-banner-side position-relative d-flex align-items-center"></div>
                <div class="shop-banner-media position-relative">
                  <img
                    loading="lazy"
                    src="{{ asset('uploads/slides/' . $slide->image) }}"
                    alt="Banner cửa hàng"
                    class="shop-banner-image"
                  />
                </div>
              </div>
            </div>
            @empty
            <div class="swiper-slide">
              <div class="shop-banner-slide">
                <div class="shop-banner-side position-relative d-flex align-items-center"></div>
                <div class="shop-banner-media position-relative">
                  <img
                    loading="lazy"
                    src="{{ asset('assets/images/shop/shop_banner3.png') }}"
                    alt="Banner cửa hàng"
                    class="shop-banner-image"
                  />
                </div>
              </div>
            </div>
            @endforelse
          </div>

          <div class="shop-banner-arrow-navigation d-flex align-items-center position-absolute">
            <button type="button" class="shop-banner-arrow shop-banner-button-prev" aria-label="Banner trước">
              <span aria-hidden="true">&larr;</span>
            </button>
            <button type="button" class="shop-banner-arrow shop-banner-button-next" aria-label="Banner tiếp theo">
              <span aria-hidden="true">&rarr;</span>
            </button>
          </div>
        </div>

        <div class="mb-3 pb-2 pb-xl-3"></div>

        <div class="d-flex justify-content-between mb-4 pb-md-2">
          <div class="breadcrumb mb-0 d-none d-md-block flex-grow-1">
            <a href="{{ route('home.index') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">Trang chủ</a>
            <span class="breadcrumb-separator menu-link fw-medium ps-1 pe-1">/</span>
            <a href="{{ route('shop') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">Cửa hàng</a>
          </div>

          <div class="shop-acs d-flex align-items-center justify-content-between justify-content-md-end flex-grow-1" style="margin-right: 20px;">
            <select class="shop-acs__select form-select w-auto border-0 py-0 order-1 order-md-0" aria-label="Kích thước trang" id="pagesize" name="pagesize">
              <option value="12" {{ (($size ?? 12) == 12) ? 'selected' : '' }}>Hiển thị</option>
              <option value="24" {{ (($size ?? 12) == 24) ? 'selected' : '' }}>24</option>
              <option value="48" {{ (($size ?? 12) == 48) ? 'selected' : '' }}>48</option>
              <option value="102" {{ (($size ?? 12) == 102) ? 'selected' : '' }}>102</option>
             
            </select>

            <select class="shop-acs__select form-select w-auto border-0 py-0 order-1 order-md-0" aria-label="Sắp xếp sản phẩm" name="orderby" id="orderby">
              <option value="-1" {{ (($order ?? -1) == -1) ? 'selected' : '' }}>Mặc định</option>
              <option value="1" {{ (($order ?? -1) == 1) ? 'selected' : '' }}>Mới - Cũ</option>
              <option value="2" {{ (($order ?? -1) == 2) ? 'selected' : '' }}>Cũ - Mới</option>
              <option value="3" {{ (($order ?? -1) == 3) ? 'selected' : '' }}>Giá, thấp - cao</option>
              <option value="4" {{ (($order ?? -1) == 4) ? 'selected' : '' }}>Giá, cao - thấp</option>
            </select>

            <div class="shop-asc__seprator mx-3 bg-light d-none d-md-block order-md-0"></div>

            <div class="col-size align-items-center order-1 d-none d-lg-flex">
              <span class="text-uppercase fw-medium me-2">Hiển thị</span>
              <button class="btn-link fw-medium me-2 js-cols-size" data-target="products-grid" data-cols="2">2</button>
              <button class="btn-link fw-medium me-2 js-cols-size" data-target="products-grid" data-cols="3">3</button>
              <button class="btn-link fw-medium js-cols-size" data-target="products-grid" data-cols="4">4</button>
            </div>

            <div class="shop-filter d-flex align-items-center order-0 order-md-3 d-lg-none">
              <button class="btn-link btn-link_f d-flex align-items-center ps-0 js-open-aside" data-aside="shopFilter">
                <svg class="d-inline-block align-middle me-2" width="14" height="10" viewBox="0 0 14 10" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <use href="#icon_filter" />
                </svg>
                <span class="text-uppercase fw-medium d-inline-block align-middle">Bộ lọc</span>
              </button>
            </div>
          </div>
        </div>

        <div class="products-grid row row-cols-2 row-cols-md-3" id="products-grid">
            @foreach ($products as $product)
          <div class="product-card-wrapper">
            <div class="product-card mb-3 mb-md-4 mb-xxl-5">
              <div class="pc__img-wrapper">
                <div class="swiper-container background-img js-swiper-slider" data-settings='{"resizeObserver": true}'>
                  <div class="swiper-wrapper">
                    <div class="swiper-slide">
                      <a href="{{ route('shop.product.details',['product_slug' => $product->slug]) }}"><img loading="lazy" src="{{ asset('uploads/products') }}/{{ $product->image }}" width="330" height="400" alt="{{ $product->name }}" class="pc__img"></a>
                    </div>
                    <div class="swiper-slide">
                        @foreach(explode(",", $product->images) as $gimg)
                      <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}"><img loading="lazy" src="{{ asset('uploads/products') }}/{{ $gimg }}" width="330" height="400" alt="{{ $product->name }}" class="pc__img"></a>
                        @endforeach
                    </div>
                  </div>
                  <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11"
                      xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_prev_sm" />
                    </svg></span>
                  <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11"
                      xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_next_sm" />
                    </svg></span>
                </div>
                @auth
                @if(Cart::instance('cart')->content()->where('id',$product->id)->count()>0)
                  <a href="{{ route('cart.index') }}" class="pc__atc btn anim_appear-bottom btn position-absolute border-0 text-uppercase fw-medium btn-warning mb-3" >Đi đến giỏ hàng</a>
                @else
                <form name="addtocart-form" method="post" action="{{ route('cart.add') }}" >
                  @csrf
                  <input type="hidden" name="id" value="{{ $product->id }}">
                  <input type="hidden" name="quantity" value="1">
                  <input type="hidden" name="name" value="{{ $product->name }}">
                  <input type="hidden" name="price" value="{{ $product->sale_price == '' ? $product->regular_price : $product->sale_price }}">
                <button type="submit" class="pc__atc btn anim_appear-bottom btn position-absolute border-0 text-uppercase fw-medium " data-aside="cartDrawer" title="Thêm vào giỏ hàng">Thêm vào giỏ hàng</button>
                </form>
                @endif
                @endauth
                @guest
                <a href="{{ route('login') }}" class="pc__atc btn anim_appear-bottom btn position-absolute border-0 text-uppercase fw-medium btn-outline-dark" title="Đăng nhập để mua">Đăng nhập</a>
                @endguest
              </div>

              <div class="pc__info position-relative">
                <p class="pc__category">{{ $product->category->name }}</p>
                <h6 class="pc__title"><a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">{{ $product->name }}</a></h6>
                <div class="product-card__price d-flex">
                  <span class="money price">
                    @if ($product->sale_price)
                        <s>{{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫</s>
                        {{ number_format((float) $product->sale_price, 0, ',', '.') }} ₫
                    @else
                        {{ number_format((float) $product->regular_price, 0, ',', '.') }} ₫
                    @endif
                  </span>
                </div>
                <div class="product-card__review d-flex align-items-center">
                  <div class="reviews-group d-flex">
                    <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_star" />
                    </svg>
                    <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_star" />
                    </svg>
                    <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_star" />
                    </svg>
                    <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_star" />
                    </svg>
                    <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_star" />
                    </svg>
                  </div>
                  <span class="reviews-note text-lowercase text-secondary ms-1">8k+ đánh giá</span>
                </div>


                @php
                  $inWishlist = auth()->check() && Cart::instance('wishlist')->content()->contains(
                    fn ($row) => (int) $row->id === (int) $product->id
                  );
                @endphp
                @auth
                @if (!$inWishlist)
                <form method="POST" action="{{ route('wishlist.add') }}" class="m-0 p-0">
                  @csrf
                  <input type="hidden" name="id" value="{{ $product->id }}"/>
                  <input type="hidden" name="name" value="{{ $product->name }}"/>
                  <input type="hidden" name="price" value="{{ $product->sale_price == '' ? $product->regular_price : $product->sale_price }}"/>
                  <input type="hidden" name="quantity" value="1"/>
                  <button type="submit" class="pc__btn-wl position-absolute top-0 end-0 bg-transparent border-0"
                    title="Thêm vào wishlist">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <use href="#icon_heart" />
                    </svg>
                  </button>
                </form>
                @else
                <button type="button" class="pc__btn-wl pc__btn-wl--in-wishlist position-absolute top-0 end-0 bg-transparent border-0"
                  disabled title="Đã trong wishlist" aria-label="Đã trong wishlist">
                  <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_heart" />
                  </svg>
                </button>
                @endif
                @endauth
                @guest
                <a href="{{ route('login') }}" class="pc__btn-wl position-absolute top-0 end-0 bg-transparent border-0 d-inline-flex align-items-center justify-content-center" title="Đăng nhập để dùng wishlist">
                  <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <use href="#icon_heart" />
                  </svg>
                </a>
                @endguest
              </div>
            </div>
          </div>
          @endforeach
        </div>
            <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wpg-pagination">
                    {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
        
      </div>
    </section>
  </main>

  <form id ="frmfiller" method="GET" action="{{route('shop.index')}}">
    @if(! empty($searchQ ?? ''))
    <input type="hidden" name="q" id="hdnSearchQ" value="{{ $searchQ }}">
    @endif
    <input type="hidden" id="page" name="page" value="{{ $products->currentPage() }}">
    <input type="hidden" id="size" name="size" value="{{ $size ?? 12 }}">
    <input type="hidden" id="order" name="orderby" value="{{ $order ?? -1 }}">
    <input type="hidden" name="brands" id="hdnBrands">
    <input type="hidden" name="categories" id="hdnCategories">
    <input type="hidden" name="min" id="hdnMin" value="{{ (int)($min_price ?? 0) }}">
    <input type="hidden" name="max" id="hdnMax" value="{{ (int)($max_price ?? 0) }}">
  </form>


@endsection

@push('scripts')
<script>
  $(function () {
    $('#pagesize').on('change', function () {
      $('#size').val($('#pagesize').val());
      $('#page').val(1);
      $('#frmfiller').submit();
    });

    
    $('#orderby').on('change', function () {
      $('#order').val($('#orderby').val());
      $('#page').val(1);
      $('#frmfiller').submit();
    });

    $("input[name='brands']").on('change', function() {
      var brands = [];
      $("input[name='brands']:checked").each(function() {
        if (brands == "") 
        {
          brands += $(this).val();
        }
        else
        {
          brands += "," + $(this).val();
        }
      });

      $('#hdnBrands').val(brands);
      $('#page').val(1);
      $('#frmfiller').submit();
     
    });

    $("input[name='categories']").on('change', function() {
      var categories = [];
      $("input[name='categories']:checked").each(function() {
        if (categories == "")
        {
          categories += $(this).val();
        }
        else
        {
          categories += "," + $(this).val();
        }
      });

      $('#hdnCategories').val(categories);
      $('#page').val(1);
      $('#frmfiller').submit();
    });

    // Price range (bootstrap-slider) -> submit như video
    $('.js-price-range').on('click', function() {
      var min = parseInt($(this).data('min') ?? 0);
      var max = parseInt($(this).data('max') ?? 1000000000);
      $('#hdnMin').val(min);
      $('#hdnMax').val(max);
      $('#page').val(1);
      $('#frmfiller').submit();
    });
  });
</script>
@endpush
