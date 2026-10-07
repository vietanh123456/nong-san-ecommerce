<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SellerRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController as CustomerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\SellerRegisterController;
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
        $query->search($request->input('search'));
    }

    $products = $query
        ->latest()
        ->paginate(8)
        ->withQueryString();

    return view('home', compact('products'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Chuyển đường dẫn /home về trang chủ
|--------------------------------------------------------------------------
*/

Route::redirect('/home', '/');

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

    // Xem trang Profile (GET)
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    // Cập nhật thông tin Profile / Đổi tên (POST hoặc PUT)
    Route::post('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profile', [ProfileController::class, 'update']);

    Route::post(
        '/address/add',
        [ProfileController::class, 'storeAddress']
    )->name('address.store');

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

    Route::get('/seller/register', [SellerRegisterController::class, 'showForm'])
        ->name('seller.register');

    Route::post('/seller/register', [SellerRegisterController::class, 'submit'])
        ->name('seller.register.submit');
});

/*
|--------------------------------------------------------------------------
| Khu vực dành cho quản trị viên
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::patch('/users/{user}/demote', [AdminUserController::class, 'demoteSeller'])
            ->name('users.demote');

        Route::get('/seller-requests', [SellerRequestController::class, 'index'])
            ->name('seller-requests.index');

        Route::patch(
            '/seller-requests/{sellerRequest}/approve',
            [SellerRequestController::class, 'approve']
        )->name('seller-requests.approve');

        Route::patch(
            '/seller-requests/{sellerRequest}/reject',
            [SellerRequestController::class, 'reject']
        )->name('seller-requests.reject');
    });

/*
|--------------------------------------------------------------------------
| Khu vực dành cho người bán
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'seller'])
    ->prefix('seller')
    ->name('seller.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )->name('dashboard');

        Route::resource(
            'products',
            SellerProductController::class
        );
    });
