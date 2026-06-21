@extends('layouts.app')
@section('content')
<main>
    <style>
      .category-banner__img {
        width: 100%;
        height: 430px;
        object-fit: cover;
      }

      .product-label--new {
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #fff;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.6px;
        line-height: 1.2;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.28);
      }

      .product-card .pc__title,
      .product-card .pc__title a {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .slideshow-arrow-navigation {
        left: 0;
        right: 0;
        top: 50%;
        bottom: auto !important;
        transform: translateY(-50%);
        width: 100%;
        z-index: 5;
        justify-content: space-between;
        margin-bottom: 0 !important;
        pointer-events: none;
      }

      .slideshow-arrow-navigation .slideshow-arrow {
        width: 42px;
        height: 42px;
        border: 1px solid #222;
        border-radius: 50%;
        background: #fff;
        color: #222;
        font-size: 22px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
        pointer-events: auto;
      }

      .slideshow-arrow-navigation .slideshow-arrow:hover,
      .slideshow-arrow-navigation .slideshow-arrow:focus {
        background: #222;
        color: #fff;
      }

      .slideshow-arrow-navigation .swiper-button-disabled {
        opacity: .35;
        cursor: default;
      }

      @media (max-width: 767.98px) {
        .category-banner__img {
          height: 280px;
        }

        .slideshow-arrow-navigation {
          top: 55%;
        }
      }
    </style>

    <section class="swiper-container js-swiper-slider slideshow" data-settings='{
        "autoplay": {
          "delay": 5000
        },
        "slidesPerView": 1,
        "effect": "fade",
        "loop": true,
        "navigation": {
          "nextEl": ".slideshow-button-next",
          "prevEl": ".slideshow-button-prev"
        }
      }'>
      <div class="swiper-wrapper">
        @foreach($slides as $slide)
        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_right-center">
              <img loading="lazy" src="{{ asset('uploads/slides/'.$slide->image) }}?v={{ optional($slide->updated_at)->timestamp }}" width="542" height="733"
                alt="Thời trang nữ 1"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              <div class="character_markup type2">
              <p class="text-uppercase font-sofia mark-grey-color animate animate_fade animate_btt animate_delay-10 mb-0">
               {{ $slide -> tagline }} 
              </p>
            </div>
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                Hàng mới về</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">{{ $slide -> title }}</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-5">{{ $slide -> subtitle }}</h2>
            <a href="{{ $slide -> link }}"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">Mua ngay</a>
            </div>
          </div>
        </div>
        @endforeach
        <!-- <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_right-center">
              <img loading="lazy" src="{{ asset('assets/images/slideshow-character1.png') }}" width="400" height="733"
                alt="Thời trang nữ 1"
                class="slideshow-character__img animate animate_fade animate_btt animate_delay-9 w-auto h-auto" />
              <div class="character_markup">
                <p class="text-uppercase font-sofia fw-bold animate animate_fade animate_rtl animate_delay-10">Mùa hè
                </p>
              </div>
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                Hàng mới về</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">Cầu vồng</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-5">PCs</h2>
              <a href="#"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">Mua ngay</a>
            </div>
          </div>
        </div>

        <div class="swiper-slide">
          <div class="overflow-hidden position-relative h-100">
            <div class="slideshow-character position-absolute bottom-0 pos_right-center">
              <img loading="lazy" src="{{ asset('assets/images/slideshow-character2.png') }}" width="400" height="690"
                alt="Thời trang nữ 2"
                class="slideshow-character__img animate animate_fade animate_rtl animate_delay-10 w-auto h-auto" />
            </div>
            <div class="slideshow-text container position-absolute start-50 top-50 translate-middle">
              <h6 class="text_dash text-uppercase fs-base fw-medium animate animate_fade animate_btt animate_delay-3">
                Hàng mới về</h6>
              <h2 class="h1 fw-normal mb-0 animate animate_fade animate_btt animate_delay-5">Ngọc cam</h2>
              <h2 class="h1 fw-bold animate animate_fade animate_btt animate_delay-5">PCs</h2>
              <a href="#"
                class="btn-link btn-link_lg default-underline fw-medium animate animate_fade animate_btt animate_delay-7">Mua ngay</a>
            </div>
          </div>
        </div> -->
      </div>

      <div class="container">
        <div
          class="slideshow-arrow-navigation d-flex align-items-center position-absolute bottom-0 mb-5">
          <button type="button" class="slideshow-arrow slideshow-button-prev" aria-label="Banner trước">
            <span aria-hidden="true">&larr;</span>
          </button>
          <button type="button" class="slideshow-arrow slideshow-button-next" aria-label="Banner tiếp theo">
            <span aria-hidden="true">&rarr;</span>
          </button>
        </div>
      </div>
    </section>
    <div class="container mw-1620 bg-white border-radius-10">
      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>
      <section class="category-carousel container">
        <h2 class="section-title text-center mb-3 pb-xl-2 mb-xl-4">Bạn có thể thích</h2>

        <div class="position-relative">
          <div class="swiper-container js-swiper-slider" data-settings='{
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": 8,
              "slidesPerGroup": 1,
              "effect": "none",
              "loop": true,
              "navigation": {
                "nextEl": ".products-carousel__next-1",
                "prevEl": ".products-carousel__prev-1"
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 2,
                  "slidesPerGroup": 2,
                  "spaceBetween": 15
                },
                "768": {
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "spaceBetween": 30
                },
                "992": {
                  "slidesPerView": 6,
                  "slidesPerGroup": 1,
                  "spaceBetween": 45,
                  "pagination": false
                },
                "1200": {
                  "slidesPerView": 8,
                  "slidesPerGroup": 1,
                  "spaceBetween": 60,
                  "pagination": false
                }
              }
            }'>
            <div class="swiper-wrapper">
              @foreach ($categories as $category)
              
           
              <div class="swiper-slide">
                <img loading="lazy" class="w-100 h-auto mb-3" src="{{ asset('uploads/categories/'.$category->image) }}" width="124"
                  onerror="this.onerror=null;this.src='{{ asset('assets/images/home/demo3/category_9.png') }}';"
                  height="124" alt="" />
                <div class="text-center">
                  <a href="{{ route('shop.index',['categories' => $category -> id]) }}" class="menu-link fw-medium">{{ $category -> name }}</a>
                </div>
              </div>
                 @endforeach
              
            </div><!-- /.swiper-wrapper -->
          </div><!-- /.swiper-container js-swiper-slider -->

          <div
            class="products-carousel__prev products-carousel__prev-1 position-absolute top-50 d-flex align-items-center justify-content-center">
            <svg width="25" height="25" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg">
              <use href="#icon_prev_md" />
            </svg>
          </div><!-- /.products-carousel__prev -->
          <div
            class="products-carousel__next products-carousel__next-1 position-absolute top-50 d-flex align-items-center justify-content-center">
            <svg width="25" height="25" viewBox="0 0 25 25" xmlns="http://www.w3.org/2000/svg">
              <use href="#icon_next_md" />
            </svg>
          </div><!-- /.products-carousel__next -->
        </div><!-- /.position-relative -->
      </section>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <section class="hot-deals container">
        <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Ưu đãi tốt</h2>
        <div class="row">
          <div
            class="col-md-6 col-lg-4 col-xl-20per d-flex align-items-center flex-column justify-content-center py-4 align-items-md-start">
            <h2>Giảm giá mùa Hè</h2>
            <h2 class="fw-bold">Giảm đến 60%</h2>

            <div class="position-relative d-flex align-items-center text-center pt-xxl-4 js-countdown mb-3"
              data-date="18-3-2024" data-time="06:50">
              <div class="day countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Ngày</span>
              </div>

              <div class="hour countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Giờ</span>
              </div>

              <div class="min countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Phút</span>
              </div>

              <div class="sec countdown-unit">
                <span class="countdown-num d-block"></span>
                <span class="countdown-word text-uppercase text-secondary">Giây</span>
              </div>
            </div>

            <a href="{{ route('shop.index') }}" class="btn-link default-underline text-uppercase fw-medium mt-3">Xem tất cả</a>
          </div>
          <div class="col-md-6 col-lg-8 col-xl-80per">
            <div class="position-relative">
              <div class="swiper-container js-swiper-slider" data-settings='{
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": 4,
                  "slidesPerGroup": 4,
                  "effect": "none",
                  "loop": false,
                  "breakpoints": {
                    "320": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 14
                    },
                    "768": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 3,
                      "spaceBetween": 24
                    },
                    "992": {
                      "slidesPerView": 3,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    },
                    "1200": {
                      "slidesPerView": 4,
                      "slidesPerGroup": 1,
                      "spaceBetween": 30,
                      "pagination": false
                    }
                  }
                }'>
                <div class="swiper-wrapper">
                  @foreach ($sproducts as $sproduct)
                  
                  
                  <div class="swiper-slide product-card product-card_style3">
                    <div class="pc__img-wrapper">
                      <a href="{{ route('shop.product.details', ['product_slug'=>$sproduct -> slug]) }}">
                        <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $sproduct -> image }}" width="258" height="313"
                          alt="{{ $sproduct -> name }}" class="pc__img">
                      </a>
                    </div>

                    <div class="pc__info position-relative">
                      <h6 class="pc__title"><a href="#">{{ $sproduct -> name }}</a></h6>
                      <div class="product-card__price d-flex">
                        <span class="money price text-secondary">
                          @if ($sproduct->sale_price)
                        <s>{{ number_format((float) $sproduct->regular_price, 0, ',', '.') }} ₫</s>
                        {{ number_format((float) $sproduct->sale_price, 0, ',', '.') }} ₫
                    @else
                        {{ number_format((float) $sproduct->regular_price, 0, ',', '.') }} ₫
                    @endif
                        </span>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div><!-- /.swiper-wrapper -->
              </div><!-- /.swiper-container js-swiper-slider -->
            </div><!-- /.position-relative -->
          </div>
        </div>
      </section>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <section class="category-banner container">
        <div class="row">
          <div class="col-md-6">
            <div class="category-banner__item border-radius-10 mb-5">
              <img loading="lazy" class="category-banner__img" src="{{ asset('assets/images/home/demo3/category_9.jpg') }}" width="690" height="665"
                alt="" />
              <div class="category-banner__item-mark">
                Bắt đầu từ $19
              </div>
              <div class="category-banner__item-content">
                <h3 class="mb-0">Màn hình ASUS TUF GAMING</h3>
                <a href="#" class="btn-link default-underline text-uppercase fw-medium">Mua ngay</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="category-banner__item border-radius-10 mb-5">
              <img loading="lazy" class="category-banner__img" src="{{ asset('assets/images/home/demo3/category_10.jpg') }}" width="690" height="665"
                alt="" />
              <div class="category-banner__item-mark">
                Bắt đầu từ $19
              </div>
              <div class="category-banner__item-content">
                <h3 class="mb-0">PC GVN Homework I3 14100</h3>
                <a href="#" class="btn-link default-underline text-uppercase fw-medium">Mua ngay</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

      <section class="products-grid container">
        <h2 class="section-title text-center mb-3 pb-xl-3 mb-xl-4">Sản phẩm nổi bật</h2>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4" id="featured-products-grid">
          @foreach ($fproducts as $fproduct)
          <div class="product-card-wrapper">
            <div class="product-card product-card_style3 mb-3 mb-md-4 mb-xxl-5">
              <div class="pc__img-wrapper">
                <a href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">
                  <img loading="lazy" src="{{ asset('uploads/products') }}/{{ $fproduct->image }}" width="330" height="400"
                    alt="{{ $fproduct->name }}" class="pc__img">
                </a>
              </div>

              <div class="pc__info position-relative">
                <h6 class="pc__title">
                  <a href="{{ route('shop.product.details', ['product_slug' => $fproduct->slug]) }}">{{ $fproduct->name }}</a>
                </h6>
                <div class="product-card__price d-flex align-items-center">
                  <span class="money price text-secondary">
                    @if ($fproduct->sale_price)
                      <s>{{ number_format((float) $fproduct->regular_price, 0, ',', '.') }} ₫</s>
                      {{ number_format((float) $fproduct->sale_price, 0, ',', '.') }} ₫
                    @else
                      {{ number_format((float) $fproduct->regular_price, 0, ',', '.') }} ₫
                    @endif
                  </span>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div><!-- /#featured-products-grid -->

        <div class="text-center mt-2">
          <a class="btn-link btn-link_lg default-underline text-uppercase fw-medium" href="#">Tải thêm</a>
        </div>
      </section>
    </div>

    <div class="mb-3 mb-xl-5 pt-1 pb-4"></div>

  </main>
@endsection
