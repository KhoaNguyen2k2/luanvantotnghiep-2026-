<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="author" content="surfside media" />
  <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animation.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('font/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('icon/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
 
    @stack("styles")
</head>

<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap">

                <!-- <div id="preload" class="preload-container">
    <div class="preloading">
        <span></span>
    </div>
</div> -->

                <div class="section-menu-left">
                    @php
                        $adminUser = auth()->user();
                        $isSuperAdmin = $adminUser && $adminUser->utype === 'ADMM';
                    @endphp
                    <div class="box-logo">
                        <a href="{{ url('/') }}/" id="site-logo-inner'">
                            <img class="" id="logo_header" alt="" src="{{ asset('images/logo/logo.png') }}"
                                data-light="{{ asset('images/logo/logo.png') }}" data-dark="{{ asset('images/logo/logo.png') }}">
                        </a>
                        <div class="button-show-hide">
                            <i class="icon-menu-left"></i>
                        </div>
                    </div>
                    <div class="center">
                        <div class="center-item">
                            <div class="center-heading">Trang chính</div>
                            <ul class="menu-list">
                                @if($isSuperAdmin)
                                    <li class="menu-item">
                                        <a href="{{ route('admin.index') }}" class="">
                                            <div class="icon"><i class="icon-grid"></i></div>
                                            <div class="text">Bảng điều khiển</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.orders') }}" class="">
                                            <div class="icon"><i class="icon-file-plus"></i></div>
                                            <div class="text">Đơn hàng</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.staff') }}" class="">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Quản lý nhân viên</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.customers') }}" class="">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Quản lý khách hàng</div>
                                        </a>
                                    </li>
                                @else
                                    <li class="menu-item">
                                        <a href="{{ route('admin.profile') }}" class="">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Trang cá nhân</div>
                                        </a>
                                    </li>
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-shopping-cart"></i></div>
                                            <div class="text">Sản phẩm</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.product.add') }}" class="">
                                                    <div class="text">Thêm sản phẩm</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.products') }}" class="">
                                                    <div class="text">Sản phẩm</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Thương hiệu</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.brand.add') }}" class="">
                                                    <div class="text">Thương hiệu mới</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.brands') }}" class="">
                                                    <div class="text">Danh sách thương hiệu</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-layers"></i></div>
                                            <div class="text">Danh mục</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.category.add') }}" class="">
                                                    <div class="text">Danh mục mới</div>
                                                </a>
                                            </li>
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.categories') }}" class="">
                                                    <div class="text">Danh mục</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><i class="icon-file-plus"></i></div>
                                            <div class="text">Đơn hàng</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="{{ route('admin.orders') }}" class="">
                                                    <div class="text">Đơn hàng</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.slides') }}" class="">
                                            <div class="icon"><i class="icon-image"></i></div>
                                            <div class="text">Slider</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.coupons') }}" class="">
                                            <div class="icon"><i class="icon-grid"></i></div>
                                            <div class="text">Mã giảm giá</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.contacts') }}" class="">
                                            <div class="icon"><i class="icon-mail"></i></div>
                                            <div class="text">Tin nhắn</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.profile') }}" class="">
                                            <div class="icon"><i class="icon-user"></i></div>
                                            <div class="text">Người dùng</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('admin.settings') }}" class="">
                                            <div class="icon"><i class="icon-settings"></i></div>
                                            <div class="text">Cài đặt</div>
                                        </a>
                                    </li>
                                @endif

                                <li class="menu-item">
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <a href="{{ route('logout') }}" class="" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <div class="icon"><i class="icon-log-out"></i></div>
                                            <div class="text">Đăng xuất</div>
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="section-content-right">

                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <a href="{{ url('/') }}/">
                                    <img class="" id="logo_header_mobile" alt="" src="{{ asset('images/logo/logo.png') }}"
                                        data-light="{{ asset('images/logo/logo.png') }}" data-dark="{{ asset('images/logo/logo.png') }}"
                                        data-width="154px" data-height="52px" data-retina="{{ asset('images/logo/logo.png') }}">
                                </a>
                                <div class="button-show-hide">
                                    <i class="icon-menu-left"></i>
                                </div>


                                @if(!$isSuperAdmin)
                                    <form class="form-search flex-grow search-field" method="GET" action="{{ route('admin.products') }}">
                                        <fieldset class="name">
                                            <input type="search" placeholder="Tìm sản phẩm..." class="show-search search-field__input" name="q" id="admin-search-input" tabindex="2" value="{{ request('q') }}" aria-required="true" autocomplete="off">
                                        </fieldset>
                                        <div class="button-submit">
                                            <button class="" type="submit"><i class="icon-search"></i></button>
                                        </div>
                                        <div class="box-content-search">
                                            <ul id="box-content-search"></ul>
                                        </div>
                                    </form>
                                @endif

                            </div>
                            <div class="header-grid">

                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="{{ asset('images/avatar/user-1.png') }}" alt="{{ $adminUser->name ?? 'Admin' }}">
                                                </span>
                                                <span class="flex flex-column">
                                                    <span class="body-title mb-2">{{ $adminUser->name ?? 'Admin' }}</span>
                                                    <span class="text-tiny">{{ $adminUser->position ?? ($isSuperAdmin ? 'Admin tổng' : 'Nhân viên') }}</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content"
                                            aria-labelledby="dropdownMenuButton3">
                                            <li>
                                                <a href="{{ route('admin.profile') }}" class="user-item">
                                                    <div class="icon">
                                                        <i class="icon-user"></i>
                                                    </div>
                                                    <div class="body-title-2">Tài khoản</div>
                                                </a>
                                            </li>
                                            <li>
                                                 <form method="POST" action="{{route('logout')}}" id="logout-form">
                                                    @csrf
                                                <a href="{{ route('logout') }}" class="user-item"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    <div class="icon">
                                                        <i class="icon-log-out"></i>
                                                    </div>
                                                    <div class="body-title-2">Đăng xuất</div>
                                                </a>
                                                 </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="main-content">
                        @yield('content')

                       


                        <div class="bottom-page">
                            <div class="body-text">B?n quy?n ? 2026 PC Parts</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>   
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>    
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        (function ($) {

            var tfLineChart = (function () {

                var chartBar = function () {

                    var options = {
                        series: [{
                            name: 'Đang chờ',
                            data: [0.00, 0.00, 0.00, 0.00, 0.00, 273.22, 208.12, 0.00, 0.00, 0.00, 0.00, 0.00]
                        }, {
                            name: '?ang ch?',
                            data: [0.00, 0.00, 0.00, 0.00, 0.00, 273.22, 208.12, 0.00, 0.00, 0.00, 0.00, 0.00]
                        },
                        {
                            name: 'Đã hủy',
                            data: [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00]
                        }, {
                            name: '?? h?y',
                            data: [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00]
                        }],
                        chart: {
                            type: 'bar',
                            height: 325,
                            toolbar: {
                                show: false,
                            },
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '10px',
                                endingShape: 'rounded'
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            show: false,
                        },
                        colors: ['#2377FC', '#FFA500', '#078407', '#FF0000'],
                        stroke: {
                            show: false,
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    colors: '#212529',
                                },
                            },
                            categories: ['Thg 1', 'Thg 2', 'Thg 3', 'Thg 4', 'Thg 5', 'Thg 6', 'Thg 7', 'Thg 8', 'Thg 9', 'Thg 10', 'Thg 11', 'Thg 12'],
                        },
                        yaxis: {
                            show: false,
                        },
                        fill: {
                            opacity: 1
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "₫ " + val + ""
                                }
                            }
                        }
                    };

                    chart = new ApexCharts(
                        document.querySelector("#line-chart-8"),
                        options
                    );
                    if ($("#line-chart-8").length > 0) {
                        chart.render();
                    }
                };

                /* Function ============ */
                return {
                    init: function () { },

                    load: function () {
                        chartBar();
                    },
                    resize: function () { },
                };
            })();

            jQuery(document).ready(function () { });

            jQuery(window).on("load", function () {
                tfLineChart.load();
            });

            jQuery(window).on("resize", function () { });
        })(jQuery);
    </script>
<script>
    window.__searchSuggestUrl = @json(route('admin.search'));
  </script>
  <script>
    (function ($) {
      var searchUrl = window.__searchSuggestUrl;
      var debounceTimer;

      function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
      }

      function buildListItems(items) {
        if (!items || !items.length) {
          return '<li class="px-2 py-2 text-secondary small">Kh?ng c? s?n ph?m ph? h?p.</li>';
        }
        return items.map(function (item) {
          return (
            '<li class="mb-2">' +
              '<a href="' + escapeHtml(item.url) + '" class="product-item text-decoration-none">' +
                '<div class="image no-bg">' +
                  '<img src="' + escapeHtml(item.thumbnail) + '" alt="" width="44" height="44" style="object-fit:cover;border-radius:8px;">' +
                '</div>' +
                '<div class="name"><span class="body-text text-dark">' + escapeHtml(item.name) + '</span></div>' +
              '</a>' +
            '</li>'
          );
        }).join('');
      }

      function fillTargets($form, items) {
        var $ul = $form.find('#box-content-search');
        if ($ul.length) {
          $ul.html(buildListItems(items));
        }
        var $div = $form.find('.search-result');
        if ($div.length) {
          $div.html('<ul class="list-unstyled mb-0">' + buildListItems(items) + '</ul>');
        }
      }

      $(document).on('input', '.search-field__input', function () {
        var $input = $(this);
        var $form = $input.closest('.search-field');
        var q = ($input.val() || '').trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) {
          $form.find('#box-content-search').empty();
          $form.find('.search-result').empty();
          return;
        }
        debounceTimer = setTimeout(function () {
          $.ajax({
            url: searchUrl,
            method: 'GET',
            data: { q: q },
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          }).done(function (data) {
            fillTargets($form, data || []);
          }).fail(function () {
            var err = '<li class="px-2 py-2 text-danger small">Không thể tìm kiếm. Thử lại sau.</li>';
            $form.find('#box-content-search').html(err);
            $form.find('.search-result').html('<ul class="list-unstyled mb-0">' + err + '</ul>');
          });
        }, 280);
      });

      $(document).on('submit', 'form.search-field', function (e) {
        var q = ($(this).find('input[name="q"]').val() || '').trim();
        if (!q) {
          e.preventDefault();
        }
      });

      $(document).on('click', '.search-popup__reset', function () {
        var $form = $(this).closest('.search-field');
        setTimeout(function () {
          $form.find('#box-content-search').empty();
          $form.find('.search-result').empty();
        }, 0);
      });
    })(jQuery);
  </script>

    @stack("scripts")
</body>


</html>

