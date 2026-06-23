@extends('layouts.app')
@section('category-strip')
  <style>
    .kcc-home {
      background: #f3f4f6;
      color: #111827;
    }
    .kcc-category-strip {
      position: sticky;
      top: 0;
      z-index: 30;
      background: #ff741f;
      color: #fff;
      box-shadow: 0 8px 24px rgba(255, 116, 31, .22);
    }
    .kcc-category-strip__inner {
      display: grid;
      grid-template-columns: repeat(12, minmax(90px, 1fr));
      gap: 0;
      overflow-x: auto;
    }
    .kcc-category-item {
      min-height: 104px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 9px;
      color: #fff;
      text-align: center;
      font-weight: 800;
      font-size: 13px;
      padding: 12px 10px;
      border-left: 1px solid rgba(255,255,255,.18);
    }
    .kcc-category-item:hover {
      background: rgba(255,255,255,.12);
      color: #fff;
    }
    .kcc-category-item img {
      width: 42px;
      height: 42px;
      object-fit: contain;
      filter: drop-shadow(0 3px 6px rgba(0,0,0,.18));
    }
    .kcc-hero {
      position: relative;
      width: 100%;
      background: #080402;
      overflow: hidden;
    }
    .kcc-hero .swiper-wrapper,
    .kcc-hero .swiper-slide {
      width: 100% !important;
    }
    .kcc-hero .swiper-slide {
      position: relative;
      min-height: clamp(320px, 27vw, 500px);
      background: #080402;
      isolation: isolate;
    }
    .kcc-hero .swiper-slide::before {
      content: "";
      position: absolute;
      inset: 0;
      z-index: -2;
      background-image: var(--hero-bg);
      background-size: cover;
      background-position: center;
      filter: blur(14px);
      transform: scale(1.06);
      opacity: .55;
    }
    .kcc-hero .swiper-slide::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: -1;
      background: linear-gradient(90deg, rgba(0,0,0,.48), rgba(0,0,0,.12) 45%, rgba(0,0,0,.48));
    }
    .kcc-hero .swiper-slide > a {
      position: relative;
      display: block;
      width: 100%;
      height: clamp(320px, 27vw, 500px);
    }
    .kcc-hero__image {
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
      display: block;
    }
    .kcc-hero__fallback {
      min-height: clamp(320px, 27vw, 500px);
      background:
        radial-gradient(circle at 68% 48%, rgba(255, 120, 28, .42), transparent 28%),
        linear-gradient(110deg, #060201 0%, #1c0904 35%, #df3c08 100%);
      color: #fff;
      display: flex;
      align-items: center;
      padding: 42px 7vw;
    }
    .kcc-hero__fallback h1 {
      color: #fff;
      font-size: clamp(38px, 6vw, 92px);
      line-height: .95;
      font-weight: 900;
      margin: 0 0 18px;
      text-transform: uppercase;
    }
    .kcc-hero__fallback p {
      color: #fed7aa;
      font-size: clamp(16px, 2vw, 26px);
      font-weight: 800;
      margin: 0 0 22px;
    }
    .kcc-hero__button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      border-radius: 999px;
      padding: 0 20px;
      background: #ff741f;
      color: #fff;
      font-weight: 900;
      text-transform: uppercase;
    }
    .kcc-hero-nav {
      position: absolute;
      inset: 0;
      pointer-events: none;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 18px;
      z-index: 4;
    }
    .kcc-hero-nav button {
      width: 44px;
      height: 44px;
      border: 1px solid rgba(255,255,255,.72);
      border-radius: 50%;
      background: rgba(0,0,0,.38);
      color: #fff;
      pointer-events: auto;
      font-size: 22px;
    }
    .kcc-promo-grid {
      margin-top: -42px;
      position: relative;
      z-index: 5;
    }
    .kcc-promo-grid__inner {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }
    .kcc-promo-card {
      display: block;
      overflow: hidden;
      border-radius: 8px;
      min-height: 140px;
      background: linear-gradient(135deg, #0f172a, #f97316);
      box-shadow: 0 16px 34px rgba(15,23,42,.18);
      color: #fff;
      position: relative;
    }
    .kcc-promo-card img {
      width: 100%;
      height: 170px;
      object-fit: cover;
      display: block;
      transition: transform .25s ease;
    }
    .kcc-promo-card:hover img {
      transform: scale(1.035);
    }
    .kcc-promo-card__fallback {
      min-height: 170px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 22px;
      background:
        radial-gradient(circle at 85% 20%, rgba(250, 204, 21, .32), transparent 30%),
        linear-gradient(135deg, #111827, #f97316);
    }
    .kcc-section {
      padding: 34px 0;
    }
    .kcc-section__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      margin-bottom: 16px;
    }
    .kcc-section__title {
      display: inline-flex;
      align-items: center;
      min-height: 42px;
      border-radius: 8px;
      padding: 0 18px;
      background: #ff741f;
      color: #fff;
      font-size: 20px;
      font-weight: 900;
      text-transform: uppercase;
      margin: 0;
    }
    .kcc-section__link {
      color: #ea580c;
      font-weight: 800;
    }
    .kcc-products {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }
    .kcc-product {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      transition: transform .18s ease, box-shadow .18s ease;
      min-width: 0;
    }
    .kcc-product:hover {
      transform: translateY(-3px);
      box-shadow: 0 18px 36px rgba(15,23,42,.1);
    }
    .kcc-product__image {
      display: block;
      aspect-ratio: 1 / 1;
      background: #f8fafc;
      padding: 14px;
    }
    .kcc-product__image img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .kcc-product__body {
      padding: 12px 14px 16px;
    }
    .kcc-product__title {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      min-height: 42px;
      margin: 0 0 10px;
      font-size: 14px;
      line-height: 1.45;
      font-weight: 800;
    }
    .kcc-product__title a {
      color: #111827;
    }
    .kcc-product__price {
      color: #ef4444;
      font-size: 15px;
      font-weight: 900;
    }
    .kcc-product__price s {
      display: block;
      color: #94a3b8;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 3px;
    }
    @media (max-width: 1199.98px) {
      .kcc-category-strip__inner {
        grid-template-columns: repeat(8, minmax(90px, 1fr));
      }
      .kcc-products {
        grid-template-columns: repeat(3, 1fr);
      }
    }
    @media (max-width: 767.98px) {
      .kcc-category-strip__inner {
        grid-template-columns: repeat(6, minmax(86px, 1fr));
      }
      .kcc-promo-grid {
        margin-top: 12px;
      }
      .kcc-promo-grid__inner,
      .kcc-products {
        grid-template-columns: repeat(2, 1fr);
      }
      .kcc-section__head {
        align-items: flex-start;
        flex-direction: column;
      }
    }
  </style>

  <section class="kcc-category-strip">
    <div class="container">
      <div class="kcc-category-strip__inner">
        @foreach($categories->take(12) as $category)
          <a class="kcc-category-item" href="{{ route('shop.index', ['categories' => $category->id]) }}">
            <img src="{{ asset('uploads/categories/' . $category->image) }}" alt="{{ $category->name }}"
              onerror="this.onerror=null;this.src='{{ asset('assets/images/home/demo3/category_9.png') }}';">
            <span>{{ $category->name }}</span>
          </a>
        @endforeach
      </div>
    </div>
  </section>
@endsection

@section('content')
<main class="kcc-home">

  <section class="swiper-container js-swiper-slider kcc-hero" data-settings='{
      "autoplay": {
        "delay": 3500,
        "disableOnInteraction": false,
        "pauseOnMouseEnter": false
      },
      "slidesPerView": 1,
      "effect": "fade",
      "speed": 700,
      "loop": true,
      "observer": true,
      "observeParents": true,
      "resizeObserver": true,
      "autoHeight": false,
      "navigation": {
        "nextEl": ".kcc-hero-next",
        "prevEl": ".kcc-hero-prev"
      }
    }'>
    <div class="swiper-wrapper">
      @forelse($slides as $slide)
        <div class="swiper-slide" style="--hero-bg: url('{{ asset('uploads/slides/' . $slide->image) }}?v={{ optional($slide->updated_at)->timestamp }}');">
          <a href="{{ $slide->link ?: route('shop.index') }}">
            <img class="kcc-hero__image" src="{{ asset('uploads/slides/' . $slide->image) }}?v={{ optional($slide->updated_at)->timestamp }}" alt="{{ $slide->title }}">
          </a>
        </div>
      @empty
        <div class="swiper-slide">
          <div class="kcc-hero__fallback">
            <div>
              <h1>Mua PC<br>Tặng ưu đãi</h1>
              <p>Build PC, gaming gear, linh kiện hiệu năng cao</p>
              <a class="kcc-hero__button" href="{{ route('shop.index') }}">Mua ngay</a>
            </div>
          </div>
        </div>
      @endforelse
    </div>
    <div class="kcc-hero-nav">
      <button type="button" class="kcc-hero-prev" aria-label="Banner trước">&larr;</button>
      <button type="button" class="kcc-hero-next" aria-label="Banner tiếp">&rarr;</button>
    </div>
  </section>

  <section class="kcc-promo-grid">
    <div class="container">
      <div class="kcc-promo-grid__inner">
        @forelse($promoSlides->take(3) as $promo)
          <a class="kcc-promo-card" href="{{ $promo->link ?: route('shop.index') }}">
            <img src="{{ asset('uploads/slides/' . $promo->image) }}?v={{ optional($promo->updated_at)->timestamp }}" alt="{{ $promo->title }}">
          </a>
        @empty
          <a class="kcc-promo-card" href="{{ route('shop.index') }}"><div class="kcc-promo-card__fallback"><strong>Màn hình gaming</strong><span>Giá tốt mỗi ngày</span></div></a>
          <a class="kcc-promo-card" href="{{ route('shop.index') }}"><div class="kcc-promo-card__fallback"><strong>Phụ kiện gaming</strong><span>Đồ xịn giá mát</span></div></a>
          <a class="kcc-promo-card" href="{{ route('build.pc.ai') }}"><div class="kcc-promo-card__fallback"><strong>Bộ PC</strong><span>Build theo ngân sách</span></div></a>
        @endforelse
      </div>
    </div>
  </section>

  <section class="kcc-section">
    <div class="container">
      <div class="kcc-section__head">
        <h2 class="kcc-section__title">Ưu đãi tốt</h2>
        <a class="kcc-section__link" href="{{ route('shop.index') }}">Xem tất cả</a>
      </div>
      <div class="kcc-products">
        @foreach($sproducts as $product)
          @include('partials.kcc-product-card', ['product' => $product])
        @endforeach
      </div>
    </div>
  </section>

  <section class="kcc-section pt-0">
    <div class="container">
      <div class="kcc-section__head">
        <h2 class="kcc-section__title">Sản phẩm nổi bật</h2>
        <a class="kcc-section__link" href="{{ route('shop.index') }}">Mua sắm ngay</a>
      </div>
      <div class="kcc-products">
        @foreach($fproducts as $product)
          @include('partials.kcc-product-card', ['product' => $product])
        @endforeach
      </div>
    </div>
  </section>
</main>
@endsection
