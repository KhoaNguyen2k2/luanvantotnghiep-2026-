<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Slide;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = (int) ($request->query('size') ?? $request->query('pagesize') ?? 12);
        $o_column = '';
        $o_order = '';
        $order = (int) ($request->query('order') ?? $request->query('orderby') ?? -1);
        $f_brands = $request->query('brands');
        $f_categories = $request->query('categories');
        $min_price = (int) ($request->query('min') ?? 0);
        $max_price = (int) ($request->query('max') ?? 1000000000);
        if ($min_price < 0) {
            $min_price = 0;
        }
        if ($max_price < $min_price) {
            $max_price = $min_price;
        }
        $allowedOrders = [-1, 1, 2, 3, 4];
        if (!in_array($order, $allowedOrders, true)) {
            $order = -1;
        }

        switch ($order) {
            case 1:
                $o_column = "created_at";
                $o_order = "DESC";
                break;
            case 2:
                $o_column = "created_at";
                $o_order = "ASC";
                break;
            case 3:
                $o_column = "sale_price";
                $o_order = "ASC";
                break;
            case 4:
                $o_column = "sale_price";
                $o_order = "DESC";
                break;
            default:
                $o_column = "created_at";
                $o_order = "DESC";
        }
        $allowedSizes = [12, 24, 48, 102];
        if (!in_array($size, $allowedSizes, true)) {
            $size = 12;
        }
        
        
        $selectedCategoryIds = [];
        $f_categories = $request->query('categories');
        if (is_array($f_categories)) {
            $selectedCategoryIds = collect($f_categories)
                ->map(fn ($v) => (int) $v)
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
            $f_categories = implode(',', $selectedCategoryIds);
        } elseif (is_string($f_categories) && trim($f_categories) !== '') {
            $selectedCategoryIds = collect(explode(',', $f_categories))
                ->map(fn ($v) => trim($v))
                ->filter(fn ($v) => $v !== '')
                ->map(fn ($v) => (int) $v)
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
            $f_categories = implode(',', $selectedCategoryIds);
        } else {
            $f_categories = '';
        }

        $selectedBrandIds = [];
        $f_brands = $request->query('brands');
        if (is_array($f_brands)) {
            $selectedBrandIds = collect($f_brands)
                ->map(fn ($v) => (int) $v)
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
            $f_brands = implode(',', $selectedBrandIds);
        } elseif (is_string($f_brands) && trim($f_brands) !== '') {
            $selectedBrandIds = collect(explode(',', $f_brands))
                ->map(fn ($v) => trim($v))
                ->filter(fn ($v) => $v !== '')
                ->map(fn ($v) => (int) $v)
                ->filter(fn ($v) => $v > 0)
                ->values()
                ->all();
            $f_brands = implode(',', $selectedBrandIds);
        } else {
            $f_brands = '';
        }

        $brands = Brand::withCount('products')
            ->orderBy('name', 'ASC')
            ->get();

        $categories = Category::withCount('products')
            ->orderBy('name', 'ASC')
            ->get();

        $query = Product::query();

        $searchQ = trim((string) $request->query('q', ''));
        if ($searchQ !== '') {
            $needle = '%' . addcslashes($searchQ, '%_\\') . '%';
            $query->where(function ($q) use ($needle) {
                $q->where('name', 'LIKE', $needle)
                    ->orWhere('slug', 'LIKE', $needle)
                    ->orWhere('SKU', 'LIKE', $needle);
            });
        }

        if (!empty($selectedCategoryIds)) {
            $query->whereIn('category_id', $selectedCategoryIds);
        }

        if (!empty($selectedBrandIds)) {
            $query->whereIn('brand_id', $selectedBrandIds);
        }

        // active price filter  (ưu tiên sale_price nếu có)
        $query->whereRaw(
            "COALESCE(sale_price, regular_price) BETWEEN ? AND ?",
            [$min_price, $max_price]
        );

        $products = $query->orderBy($o_column, $o_order)
            ->paginate($size)
            ->withQueryString();

        $shopBannerLimit = SiteSetting::getInt('shop_banner_limit', 5);
        $shopSlides = Slide::where('placement', 'shop')
            ->where('status', '1')
            ->take($shopBannerLimit)
            ->get();

        return view('shop', compact(
            'products',
            'shopBannerLimit',
            'shopSlides',
            'size',
            'order',
            'brands',
            'f_brands',
            'selectedBrandIds',
            'categories',
            'f_categories',
            'selectedCategoryIds',
            'min_price',
            'max_price',
            'searchQ'
        ));
    }

    public function product_details($product_slug)
    {
        $product = Product::where('slug',$product_slug)->first();
        
        // related things
        $relatedProducts = $product ? $product->getRelatedProducts(8) : collect();
        
        return view('product-details', compact('product', 'relatedProducts'));
    }
}
