<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $homeBannerLimit = SiteSetting::getInt('home_banner_limit', 5);
        $slides = Slide::where('placement', 'home')
            ->where('status', '1')
            ->take($homeBannerLimit)
            ->get();
        $promoSlides = Slide::where('placement', 'promo')
            ->where('status', '1')
            ->orderBy('id', 'DESC')
            ->take(6)
            ->get();
        $categories = Category::orderBy('name')->get();
        $sproducts = Product::whereNotNull('sale_price')
            ->where('sale_price', '<>', '')
            ->inRandomOrder()
            ->take(8)
            ->get();
        $fproducts = Product::where('featured', 1)->take(8)->get();
        return view('index',compact('slides','promoSlides','categories','sproducts','fproducts'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contact_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->comment = $request->comment;
        $contact->save();
        return redirect()->back()->with('success', 'Tin nhắn của bạn đã được gửi thành công!');
    }

    // public function about()
    // {
    //     return view('about');
    // }   

        public function search(Request $request)
        {
            $query = trim((string) (
                $request->query('q')
                ?? $request->query('query')
                ?? $request->input('search-keyword', '')
            ));

            if ($query === '' || Str::length($query) < 2) {
                return response()->json([]);
            }

            $needle = '%' . addcslashes($query, '%_\\') . '%';

            $results = Product::query()
                ->where(function ($q) use ($needle) {
                    $q->where('name', 'LIKE', $needle)
                        ->orWhere('slug', 'LIKE', $needle)
                        ->orWhere('SKU', 'LIKE', $needle);
                })
                ->orderBy('name')
                ->take(12)
                ->get();

            $payload = $results->map(static function ($product) {
                $thumbnail = $product->image
                    ? asset('uploads/products/thumbnails/' . $product->image)
                    : asset('assets/images/home/demo3/category_9.png');

                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'url' => route('shop.product.details', ['product_slug' => $product->slug]),
                    'thumbnail' => $thumbnail,
                ];
            });

            return response()->json($payload);
        }

    
}
