<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PcBuildController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;






Auth::routes();

Route::get('/login/customer', [AuthLoginController::class, 'showLoginForm'])->name('login.customer');
Route::get('/login/staff', [AuthLoginController::class, 'showLoginForm'])->name('login.staff');

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop-alias', function () {return redirect()->route('shop.index');})->name('shop');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/wishlist/add', function () {
    return Auth::check()
        ? redirect()->route('wishlist.index')
        : redirect()->guest(route('login'));
});





Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-details', [UserController::class, 'account_details'])->name('user.account.details');
    Route::put('/account-details', [UserController::class, 'account_details_update'])->name('user.account.details.update');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_cancel'])->name('user.order.cancel');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
    Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
    Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
    Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
    Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');

    Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
    Route::post('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');

    Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
    Route::post('/wishlist/remove', [WishlistController::class, 'remove_from_wishlist'])->name('wishlist.remove');
    Route::post('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'move_to_cart'])->name('wishlist.move_to_cart');
    Route::post('/wishlist/move-all-to-cart', [WishlistController::class, 'move_all_to_cart'])->name('wishlist.move_all_to_cart');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/place-an-order', function () {
        return redirect()->route('cart.checkout');
    });
    Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order'); 
    Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

    Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');

    Route::post('/contact/store', [HomeController::class, 'contact_store'])->name('home.contact.store');

});

Route::get('/build-pc-ai', [PcBuildController::class, 'index'])->name('build.pc.ai');
Route::post('/build-pc-ai', [PcBuildController::class, 'analyze'])->name('build.pc.ai.analyze');
Route::post('/build-pc-ai/chat', [PcBuildController::class, 'chat'])->name('build.pc.ai.chat');
Route::post('/build-pc-ai/recommend', [PcBuildController::class, 'aiRecommend'])->name('build.pc.ai.recommend');
Route::post('/build-pc-ai/checkout', [PcBuildController::class, 'addFromBuild'])->name('cart.add.from.build');

Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::put('/admin/profile', [AdminController::class, 'profile_update'])->name('admin.profile.update');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');  
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update'); 
    Route::delete('/admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete'); 

    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');

    Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');

    Route::get('/admin/category/{id}/edit', [AdminController::class, 'category_edit'])->name('admin.category.edit');

    Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');

    Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');

    Route::get('/admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');

    Route::get('/admin/product/store', function () {return redirect()->route('admin.product.add');});

    Route::post('/admin/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');

    Route::get('/admin/product/{id}/edit', [AdminController::class, 'product_edit'])->name('admin.product.edit');

    Route::get('/admin/product/{id}/update', function ($id) {return redirect()->route('admin.product.edit', ['id' => $id]);});

    Route::put('/admin/product/{id}/update', [AdminController::class, 'product_update'])->name('admin.product.update');

    Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');

    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');

    Route::get('/admin/coupon/add', [AdminController::class, 'coupon_add'])->name('admin.coupon.add');

    Route::post('/admin/coupon/store', [AdminController::class, 'coupon_store'])->name('admin.coupon.store');

    Route::get('/admin/coupon/{id}/edit', [AdminController::class, 'coupon_edit'])->name('admin.coupon.edit');

    Route::get('/admin/coupon/{id}/update', function ($id) {
        return redirect()->route('admin.coupon.edit', ['id' => $id]);
    });

    Route::put('/admin/coupon/{id}/update', [AdminController::class, 'coupon_update'])->name('admin.coupon.update');

    Route::delete('/admin/coupon/{id}/delete', [AdminController::class, 'coupon_delete'])->name('admin.coupon.delete');

    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');

    Route::get('/admin/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order.details');

    Route::match(['get', 'put'], '/admin/order/{order_id}/update-status', [AdminController::class, 'order_update_status'])->name('admin.order.update_status');

    Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides'); 

    Route::get('/admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');

    Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    
    Route::get('/admin/slide/{id}/edit', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');

    Route::put('/admin/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');

    Route::delete('/admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

    Route::get('/admin/contact', [AdminController::class, 'contacts'])->name('admin.contacts');

    Route::delete('/admin/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');

    Route::get('/admin/staff', [AdminController::class, 'staff'])->name('admin.staff');
    Route::get('/admin/staff/add', [AdminController::class, 'staff_add'])->name('admin.staff.add');
    Route::post('/admin/staff/store', [AdminController::class, 'staff_store'])->name('admin.staff.store');
    Route::get('/admin/staff/{id}/edit', [AdminController::class, 'staff_edit'])->name('admin.staff.edit');
    Route::put('/admin/staff/{id}/update', [AdminController::class, 'staff_update'])->name('admin.staff.update');
    Route::delete('/admin/staff/{id}/delete', [AdminController::class, 'staff_delete'])->name('admin.staff.delete');

    Route::get('/admin/customers', [AdminController::class, 'customers'])->name('admin.customers');

    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::put('/admin/settings', [AdminController::class, 'settings_update'])->name('admin.settings.update');

    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');

    Route::get('/payment', [PaymentController::class, 'index']);

    Route::match(['get', 'post'], '/vnpay-create', [PaymentController::class, 'createPayment'])->name('vnpay.create');

    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn'])->name('vnpay.return');





 
    

});
