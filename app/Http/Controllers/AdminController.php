<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UpdatesShippingProfile;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Slide;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    use UpdatesShippingProfile;

    private function generateImageFileName($file): string
    {
        return Carbon::now()->format('YmdHisv') . '-' . Str::lower(Str::random(8)) . '.' . $file->extension();
    }

    private function isSuperAdmin(): bool
    {
        return Auth::check()
            && Auth::user()->utype === 'ADMM'
            && strtolower(Auth::user()->email) === 'admint@lvtn.vn';
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless($this->isSuperAdmin(), 403);
    }

    public function profile()
    {
        $user = Auth::user();

        return view('admin.profile', compact('user'));
    }

    public function profile_update(Request $request)
    {
        $user = Auth::user();
        $emailRules = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];

        if ($user->utype === 'ADM') {
            $emailRules[] = function ($attribute, $value, $fail) {
                if (! str_ends_with(strtolower((string) $value), '@tttn.vn')) {
                    $fail('Email nhân viên phải có đuôi @tttn.vn.');
                }
            };
        }

        if ($user->utype === 'ADMM') {
            $emailRules[] = function ($attribute, $value, $fail) {
                if (! str_ends_with(strtolower((string) $value), '@lvtn.vn')) {
                    $fail('Email admin tổng phải có đuôi @lvtn.vn.');
                }
            };
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($user->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'position' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->mobile = $data['mobile'];
        $user->birth_date = $data['birth_date'] ?? null;
        $user->position = $data['position'];

        if ($request->filled('password')) {
            $user->password = Hash::make((string) $request->password);
        }

        $user->save();

        return redirect()->route('admin.profile')->with('status', 'Đã cập nhật thông tin cá nhân.');
    }

    public function index()
    {
        if (! $this->isSuperAdmin()) {
            return redirect()->route('admin.profile');
        }
        
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");

        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
        sum(if(status = 'ordered',total,0)) as TotalOrderedAmount,
        sum(if(status = 'delivered',total,0)) as TotalDeliveredAmount,
        sum(if(status = 'canceled',total,0)) as TotalCanceledAmount,
        Count(*) as Total, 
        sum(if(status = 'ordered',1,0)) as TotalOrdered,
        sum(if(status = 'delivered',1,0)) as TotalDelivered,
        sum(if(status = 'canceled',1,0)) as TotalCanceled
        From Orders
        ");

        $monthlyDatas = DB::select("SELECT M.id as MonthNo, M.name as MonthName,
        Ifnull(D.TotalAmount, 0) as TotalAmount,
        Ifnull(D.TotalOrderedAmount, 0) as TotalOrderedAmount,
        Ifnull(D.TotalDeliveredAmount, 0) as TotalDeliveredAmount,
        Ifnull(D.TotalCanceledAmount, 0) as TotalCanceledAmount from month_names M
        left join (select Date_format(created_at, '%b') as MonthName,
        Month(created_at) as MonthNo,
        sum(total) As TotalAmount,
        sum(if(status = 'ordered',total,0)) as TotalOrderedAmount,
        sum(if(status = 'delivered',total,0)) as TotalDeliveredAmount,
        sum(if(status = 'canceled',total,0)) as TotalCanceledAmount
        from Orders where Year(created_at) = Year(now()) group by Year(created_at), Month(created_at), DATE_FORMAT('created_at','%b') 
        order by month(created_at)) D on D.MonthNo = M.id");

        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $orderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());   

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');


        return view('admin.index',compact('orders', 'dashboardDatas', 'AmountM', 'orderedAmountM', 'DeliveredAmountM', 'CanceledAmountM', 'TotalAmount', 'TotalOrderedAmount', 'TotalDeliveredAmount', 'TotalCanceledAmount'));
    }

    public function staff()
    {
        $this->authorizeSuperAdmin();

        $staff = User::where('utype', 'ADM')->orderBy('id', 'DESC')->paginate(12);
        return view('admin.staff', compact('staff'));
    }

    public function staff_add()
    {
        $this->authorizeSuperAdmin();

        return view('admin.staff-add');
    }

    public function staff_store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                function ($attribute, $value, $fail) {
                    if (! str_ends_with(strtolower((string) $value), '@tttn.vn')) {
                        $fail('Email nhân viên phải có đuôi @tttn.vn.');
                    }
                },
            ],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'position' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'birth_date' => $data['birth_date'] ?? null,
            'position' => $data['position'],
            'utype' => 'ADM',
            'password' => Hash::make((string) $data['password']),
        ]);

        return redirect()->route('admin.staff')->with('status', 'Đã thêm nhân viên.');
    }

    public function staff_edit($id)
    {
        $this->authorizeSuperAdmin();

        $staffMember = User::where('utype', 'ADM')->findOrFail($id);
        return view('admin.staff-edit', compact('staffMember'));
    }

    public function staff_update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $staffMember = User::where('utype', 'ADM')->findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffMember->id),
                function ($attribute, $value, $fail) {
                    if (! str_ends_with(strtolower((string) $value), '@tttn.vn')) {
                        $fail('Email nhân viên phải có đuôi @tttn.vn.');
                    }
                },
            ],
            'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($staffMember->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'position' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $staffMember->name = $data['name'];
        $staffMember->email = $data['email'];
        $staffMember->mobile = $data['mobile'];
        $staffMember->birth_date = $data['birth_date'] ?? null;
        $staffMember->position = $data['position'];

        if ($request->filled('password')) {
            $staffMember->password = Hash::make((string) $request->password);
        }

        $staffMember->save();

        return redirect()->route('admin.staff')->with('status', 'Đã cập nhật nhân viên.');
    }

    public function staff_delete($id)
    {
        $this->authorizeSuperAdmin();

        User::where('utype', 'ADM')->findOrFail($id)->delete();
        return redirect()->route('admin.staff')->with('status', 'Đã xóa nhân viên.');
    }

    public function customers()
    {
        $this->authorizeSuperAdmin();

        $customers = User::where('utype', 'USR')->orderBy('id', 'DESC')->paginate(12);
        return view('admin.customers', compact('customers'));
    }

    public function settings()
    {
        $settings = [
            'home_banner_limit' => SiteSetting::getInt('home_banner_limit', 5),
            'shop_banner_limit' => SiteSetting::getInt('shop_banner_limit', 5),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function settings_update(Request $request)
    {
        $data = $request->validate([
            'home_banner_limit' => ['required', 'integer', 'min:1', 'max:5'],
            'shop_banner_limit' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        SiteSetting::putValue('home_banner_limit', $data['home_banner_limit']);
        SiteSetting::putValue('shop_banner_limit', $data['shop_banner_limit']);

        return redirect()->route('admin.settings')->with('status', 'Đã cập nhật cài đặt banner.');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240|dimensions:min_width=124,min_height=124',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_name = $this->generateImageFileName($image);
        $this->GenerateBrandThumbaislImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'ÄÃ£ thÃªm thÆ°Æ¡ng hiá»‡u thÃ nh cÃ´ng!');

    }


    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
       $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240|dimensions:min_width=124,min_height=124',
        ]);

        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        if($request->hasFile('image')) {
           if(File::exists(public_path('uploads/brands/'). '/' . $brand->image)) {
                File::delete(public_path('uploads/brands/'). '/' . $brand->image);
            }
            $image = $request->file('image');
        $file_name = $this->generateImageFileName($image);
        $this->GenerateBrandThumbaislImage($image, $file_name);
        $brand->image = $file_name;
        }
        
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'ÄÃ£ cáº­p nháº­t thÆ°Æ¡ng hiá»‡u thÃ nh cÃ´ng!');
    }

    public function GenerateBrandThumbaislImage($image, $imageName)
    {
      $destinationPath = public_path('uploads/brands');

      if (!File::exists($destinationPath)) {
          File::makeDirectory($destinationPath, 0755, true);
      }

      $img = Image::read($image->path());
      $img->cover(124, 124, "top")
          ->sharpen(8)
          ->save($destinationPath . '/' . $imageName, quality: 92);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if(File::exists(public_path('uploads/brands/'). '/' . $brand->image)) {
            File::delete(public_path('uploads/brands/'). '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'ÄÃ£ xÃ³a thÆ°Æ¡ng hiá»‡u thÃ nh cÃ´ng!');
    }
    
    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240|dimensions:min_width=124,min_height=124',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        $image = $request->file('image');
        $file_name = $this->generateImageFileName($image);
        $this->GenerateCategoryThumbnailImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'ÄÃ£ thÃªm danh má»¥c thÃ nh cÃ´ng!');
    }

    public function GenerateCategoryThumbnailImage($image, $imageName)
    {
      $destinationPath = public_path('uploads/categories');
      if (!File::exists($destinationPath)) {
          File::makeDirectory($destinationPath, 0755, true);
      }
      $img = Image::read($image->path());
      $img->cover(124, 124, "top")
          ->sharpen(8)
          ->save($destinationPath . '/' . $imageName, quality: 92); 
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
       $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240|dimensions:min_width=124,min_height=124',
        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        if($request->hasFile('image')) {
           if(File::exists(public_path('uploads/categories/'). '/' . $category->image)) {
                File::delete(public_path('uploads/categories/'). '/' . $category->image);
            }
            $image = $request->file('image');
        $file_name = $this->generateImageFileName($image);
        $this->GenerateCategoryThumbnailImage($image, $file_name);
        $category->image = $file_name;
        }
        
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'ÄÃ£ cáº­p nháº­t danh má»¥c thÃ nh cÃ´ng!');
    }
    

    public function category_delete($id)
    {
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories/'). '/' . $category->image)) {
            File::delete(public_path('uploads/categories/'). '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'ÄÃ£ xÃ³a danh má»¥c thÃ nh cÃ´ng!');
    }

    public function products(Request $request)
    {
        $searchQ = trim((string) $request->query('q', ''));

        $query = Product::query();
        if ($searchQ !== '') {
            $needle = '%' . addcslashes($searchQ, '%_\\') . '%';
            $query->where(function ($q) use ($needle) {
                $q->where('name', 'LIKE', $needle)
                    ->orWhere('slug', 'LIKE', $needle)
                    ->orWhere('SKU', 'LIKE', $needle);
            });
        }

        $products = $query->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products', compact('products', 'searchQ'));
    }   

    public function product_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-add', compact('categories', 'brands'));
    }

    public function product_store(Request $request)
    {
        $request->merge([
            'regular_price' => $this->normalizePrice($request->regular_price),
            'sale_price' => $this->normalizePrice($request->sale_price),
            'stock_status' => $request->stock_status === 'instock' ? 'in_stock' : ($request->stock_status === 'outofstock' ? 'out_of_stock' : $request->stock_status),
        ]);

        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|lte:regular_price',
            'SKU' => 'required',
            'stock_status' => 'required|in:in_stock,out_of_stock',
            'featured' => 'required',
            'quantity' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);  

        // LÆ°u sáº£n pháº©m vÃ o database
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->quantity = $request->quantity;
        $product->stock_status = $request->stock_status;
        $product->featured = (int) $request->featured;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        // Use a collision-resistant base name so multiple products created in the same second
        // won't overwrite each other's images.
        $baseName = Carbon::now()->format('YmdHisv') . '-' . Str::lower(Str::random(8));

        // Xá»­ lÃ½ áº£nh sáº£n pháº©m
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $baseName . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images')) {
            $allowFileExtensions = ['jpeg', 'png', 'jpg', 'gif', 'webp'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension ();
                $gcheck = in_array($extension, $allowFileExtensions);
                if ($gcheck) {
                    $gfileName = $baseName . '-' . $counter . '.' . $extension;
                    $this->GenerateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(",", $gallery_arr);

        }
        // LÆ°u sáº£n pháº©m vÃ o database
        
        $product->images = $gallery_images;
        $product->save();

        return redirect()->route('admin.products')->with('status', 'ÄÃ£ thÃªm sáº£n pháº©m thÃ nh cÃ´ng!');
    }


    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($destinationPathThumbnail)) {
            File::makeDirectory($destinationPathThumbnail, 0755, true);
        }

        $fullImage = Image::read($image->path());

        // Save higher quality + slight sharpening to avoid blur after resizing.
        $fullImage->cover(600, 600, "top")
            ->sharpen(8)
            ->save($destinationPath . '/' . $imageName, quality: 92);

        $thumbnailImage = Image::read($image->path());
        $thumbnailImage->cover(104, 104, "top")
            ->sharpen(10)
            ->save($destinationPathThumbnail . '/' . $imageName, quality: 92);
    }

    private function normalizePrice($value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Accept formats like:
        // - 21.430.000
        // - 18,490,000Ä‘
        // - 21430000.00 (from DB edit form)
        // We store VND as integer amount (no decimals).
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        // If the value looks like a simple decimal number (e.g. 21430000.00),
        // keep only the integer part to avoid multiplying by 100 when removing the dot.
        if (preg_match('/^\d+(?:[.,]\d{1,2})$/', $raw)) {
            $parts = preg_split('/[.,]/', $raw, 2);
            $intPart = $parts[0] ?? '';
            return $intPart === '' ? null : $intPart;
        }

        // Otherwise treat separators as thousands separators and keep digits only.
        $digitsOnly = preg_replace('/\D+/', '', $raw);
        return $digitsOnly === '' ? null : $digitsOnly;
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    public function product_update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->merge([
            'regular_price' => $this->normalizePrice($request->regular_price),
            'sale_price' => $this->normalizePrice($request->sale_price),
        ]);

        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $product->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|lte:regular_price',
            'SKU' => 'required',
            'stock_status' => 'required|in:in_stock,out_of_stock',
            'featured' => 'required|in:0,1',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->quantity = $request->quantity;
        $product->stock_status = $request->stock_status;
        $product->featured = (int) $request->featured;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $baseName = Carbon::now()->format('YmdHisv') . '-' . Str::lower(Str::random(8));

        if ($request->hasFile('image')) {
            if ($product->image) {
                $path1 = public_path('uploads/products/' . $product->image);
                $path2 = public_path('uploads/products/thumbnails/' . $product->image);
                if (File::exists($path1)) File::delete($path1);
                if (File::exists($path2)) File::delete($path2);
            }

            $image = $request->file('image');
            $imageName = $baseName . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        if ($request->hasFile('images')) {
            $allowFileExtensions = ['jpeg', 'png', 'jpg', 'gif', 'webp'];
            $files = $request->file('images');

            $existing = [];
            if (!empty($product->images)) {
                $existing = array_values(array_filter(array_map('trim', explode(',', (string) $product->images))));
            }

            $counter = 1;
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if (!in_array($extension, $allowFileExtensions)) {
                    continue;
                }
                $gfileName = $baseName . '-' . $counter . '.' . $extension;
                $this->GenerateProductThumbnailImage($file, $gfileName);
                $existing[] = $gfileName;
                $counter++;
            }

            $existing = array_values(array_unique(array_filter(array_map('trim', $existing))));
            $product->images = implode(',', $existing);
        }

        // Remove selected gallery images
        if ($request->filled('remove_images') && is_array($request->remove_images)) {
            $toRemove = array_values(array_unique(array_filter(array_map('trim', $request->remove_images))));
            if (!empty($toRemove)) {
                $current = [];
                if (!empty($product->images)) {
                    $current = array_values(array_filter(array_map('trim', explode(',', (string) $product->images))));
                }

                $current = array_values(array_diff($current, $toRemove));
                $product->images = implode(',', $current);

                foreach ($toRemove as $file) {
                    $p1 = public_path('uploads/products/' . $file);
                    $p2 = public_path('uploads/products/thumbnails/' . $file);
                    if (File::exists($p1)) File::delete($p1);
                    if (File::exists($p2)) File::delete($p2);
                }
            }
        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'ÄÃ£ cáº­p nháº­t sáº£n pháº©m thÃ nh cÃ´ng!');
    }


    public function product_delete($id)
    {
        $product = Product::find($id);
        if(File::exists(public_path('uploads/products'). '/' . $product->image)) {
            File::delete(public_path('uploads/products'). '/' . $product->image);
        }
        if(File::exists(public_path('uploads/products/thumbnails'). '/' . $product->image)) {
            File::delete(public_path('uploads/products/thumbnails'). '/' . $product->image);
        }

        foreach (explode(',', (string) $product->images) as $ofile) {
            if(File::exists(public_path('uploads/products'). '/' . $ofile)) {
                File::delete(public_path('uploads/products'). '/' . $ofile);
            }
            if(File::exists(public_path('uploads/products/thumbnails'). '/' . $ofile)) {
                File::delete(public_path('uploads/products/thumbnails'). '/' . $ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'ÄÃ£ xÃ³a sáº£n pháº©m thÃ nh cÃ´ng!');
    }

    public function coupons()
    {
        $coupons = Coupon::with('category')->orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.coupon-add', compact('categories'));
    }

    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric|min:0',
            'scope' => 'required|in:order,category',
            'category_id' => 'required_if:scope,category|nullable|exists:categories,id',
            'expiry_date' => 'required|date_format:Y-m-d',
        ]);

        $coupon = new Coupon();
        $coupon->code = trim($request->code);
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->scope = $request->scope;
        $coupon->category_id = $request->scope === Coupon::SCOPE_CATEGORY ? $request->category_id : null;
        $coupon->expiry_date = $request->date('expiry_date');
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'ÄÃ£ thÃªm coupon thÃ nh cÃ´ng!');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('admin.coupon-edit', compact('coupon', 'categories'));
    }

    public function coupon_update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric|min:0',
            'scope' => 'required|in:order,category',
            'category_id' => 'required_if:scope,category|nullable|exists:categories,id',
            'expiry_date' => 'required|date_format:Y-m-d',
        ]);

        $coupon->code = trim($request->code);
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->scope = $request->scope;
        $coupon->category_id = $request->scope === Coupon::SCOPE_CATEGORY ? $request->category_id : null;
        $coupon->expiry_date = $request->date('expiry_date');
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'ÄÃ£ cáº­p nháº­t coupon thÃ nh cÃ´ng!');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('status', 'ÄÃ£ xÃ³a coupon thÃ nh cÃ´ng!');
    }

    public function orders()
    {
        $orders = Order::with('orderItems')->orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderItems = OrderItem::with(['product.category', 'product.brand'])
            ->where('order_id', $order_id)
            ->orderBy('id')
            ->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();

        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }

    public function order_update_status(Request $request, $order_id)
    {
        if (!$request->isMethod('put')) {
            return redirect()->route('admin.order.details', ['order_id' => $order_id]);
        }

        $request->validate([
            'status' => 'required|in:ordered,delivered,canceled',
        ]);

        $order = Order::findOrFail($order_id);
        $order->status = $request->status;
        if($request->status === 'delivered')
            {
                $order->delivered_date = Carbon::now();
                $order->canceled_date = null;
            }
        else if($request->status === 'canceled')
            {
                $order->canceled_date = Carbon::now();
                $order->delivered_date = null;
            }   
        else {
                $order->delivered_date = null;
                $order->canceled_date = null;
            }
        $order->save();

        if($request->status === 'delivered')
            {
                $transaction = Transaction::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'user_id' => $order->user_id,
                        'mode' => 'cod',
                        'status' => 'pending',
                    ]
                );
                $transaction->status = 'approved';
                $transaction->save();
            }
            return redirect()
                ->route('admin.order.details', ['order_id' => $order->id])
                ->with('status', 'Cáº­p nháº­t tráº¡ng thÃ¡i Ä‘Æ¡n hÃ ng thÃ nh cÃ´ng!');
    }

    public function slides()
    {
        $homeSlides = Slide::where('placement', 'home')->orderBy('id', 'DESC')->get();
        $shopSlides = Slide::where('placement', 'shop')->orderBy('id', 'DESC')->get();

        return view('admin.slides', compact('homeSlides', 'shopSlides'));
        
    }   

    public function slide_add()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',   
            'link' => 'required|string|max:255',
            'placement' => ['required', Rule::in(['home', 'shop'])],
            'status' => 'required|in:0,1',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->placement = $request->placement;
        $slide->status = (int) $request->status;


        $image = $request->file('image');
        $file_name = $this->generateImageFileName($image);
        $this->GenerateSlideThumbnailImage($image, $file_name, $slide->placement);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Đã thêm slide');
    }

    public function GenerateSlideThumbnailImage($image, $imageName, string $placement = 'home')
    {
      $destinationPath = public_path('uploads/slides');
      if (!File::exists($destinationPath)) {
          File::makeDirectory($destinationPath, 0755, true);
      }
      $img = Image::read($image->path());
      if ($placement === 'shop') {
          $img->cover(1440, 480, "center")
              ->sharpen(10)
              ->save($destinationPath . '/' . $imageName, quality: 95);

          return;
      }

      $img->cover(900, 1550, "top")
          ->sharpen(10)
          ->save($destinationPath . '/' . $imageName, quality: 95); 
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
         $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',   
            'link' => 'required|string|max:255',
            'placement' => ['required', Rule::in(['home', 'shop'])],
            'status' => 'required|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->placement = $request->placement;
        $slide->status = (int) $request->status;

        if($request->hasFile('image')) {
            if(File::exists(public_path('uploads/slides'). '/' . $slide->image)) {
                File::delete(public_path('uploads/slides'). '/' . $slide->image);
            }   
            $image = $request->file('image');
            $file_name = $this->generateImageFileName($image);
            $this->GenerateSlideThumbnailImage($image, $file_name, $slide->placement);
            $slide->image = $file_name;
         }
        
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Đã cập nhật slide');
    }

    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if(File::exists(public_path('uploads/slides'). '/' . $slide->image)) {
            File::delete(public_path('uploads/slides'). '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Đã xóa slide');
    }

    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }

    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with('status', 'ÄÃ£ xÃ³a liÃªn há»‡ thÃ nh cÃ´ng!');
    }

    public function search(Request $request)
    {
        $query = trim((string) ($request->query('q') ?? $request->query('query') ?? ''));

        if ($query === '' || mb_strlen($query) < 2) {
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
            ->take(10)
            ->get();

        $payload = $results->map(static function ($product) {
            $thumb = $product->image
                ? asset('uploads/products/thumbnails/' . $product->image)
                : asset('images/logo/logo.png');

            return [
                'name' => $product->name,
                'sku' => $product->SKU,
                'thumbnail' => $thumb,
                'url' => route('admin.product.edit', ['id' => $product->id]),
            ];
        });

        return response()->json($payload);
    }

}

