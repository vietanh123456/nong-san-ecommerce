<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tự động đếm và truyền số lượng Yêu thích ra toàn bộ các trang
        View::composer('*', function ($view) {
            $wishlistCount = 0;
            
            if (Auth::check()) {
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
            } else {
                $wishlistCount = count(session()->get('wishlist', []));
            }

            $view->with('wishlistCount', $wishlistCount);
        });
    }
}