<?php

namespace App\Providers;

use App\Models\Wishlist;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Tự động đếm và truyền số lượng Yêu thích ra toàn bộ các trang
        View::composer('*', function ($view) {
            $wishlistCount = 0;
            $cartCount = array_sum(array_column(session('cart', []), 'quantity'));

            if (Auth::check()) {
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
            } else {
                $wishlistCount = count(session()->get('wishlist', []));
            }

            $view->with('wishlistCount', $wishlistCount);
            $view->with('cartCount', $cartCount);
        });
    }
}
