<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $id = (string) $event->user->getAuthIdentifier();
            Cart::instance('cart')->restore($id);
            Cart::instance('wishlist')->restore($id);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user === null) {
                return;
            }
            $id = (string) $event->user->getAuthIdentifier();
            Cart::instance('cart')->store($id);
            Cart::instance('wishlist')->store($id);
        });
    }
}
