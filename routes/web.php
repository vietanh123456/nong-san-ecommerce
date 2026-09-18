<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController as CustomerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\WishlistController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trang chủ
|--------------------------------------------------------------------------
*/

Route::get('/', function (Request $request) {
    $query = Product::query();

    if ($request->filled('search')) {
        $search = trim($request->input('search'));

        $query->where(function ($query) use ($search): void {
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $products = $query
        ->latest()
        ->get();

    return view('home', compact('products'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Sản phẩm dành cho khách hàng
|--------------------------------------------------------------------------
*/

Route::get('/products', [CustomerProductController::class, 'index'])
    ->name('products.index');

Route::get('/products/{id}', [CustomerProductController::class, 'show'])
    ->name('products.show');

/*
|--------------------------------------------------------------------------
| Đăng nhập và đăng ký
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Giỏ hàng sử dụng session
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/cart/add/{product}', [CartController::class, 'add'])
    ->name('cart.add');

Route::patch('/cart/update/{product}', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])
    ->name('cart.remove');

/*
|--------------------------------------------------------------------------
| Chức năng yêu cầu đăng nhập
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    Route::post('/address/add', [ProfileController::class, 'storeAddress'])
        ->name('address.store');

    Route::delete(
        '/address/delete/{address}',
        [ProfileController::class, 'destroyAddress']
    )->name('address.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index');

    Route::post(
        '/wishlist/toggle/{product}',
        [WishlistController::class, 'toggle']
    )->name('wishlist.toggle');

    Route::post(
        '/products/{product}/reviews',
        [ReviewController::class, 'store']
    )->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| Khu vực dành cho người bán
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('seller')
    ->name('seller.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource(
            'products',
            SellerProductController::class
        );
    });