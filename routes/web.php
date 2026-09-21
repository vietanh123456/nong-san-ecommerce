<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CertificateFileController;
use App\Http\Controllers\ProductController as CustomerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductBatchController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\TraceController;
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

Route::get('/trace/{batchCode}', [TraceController::class, 'show'])
    ->name('trace.show');

Route::get('/trace/certificates/{certificate}', [
    CertificateFileController::class,
    'public',
])->name('trace.certificates.show');

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

        Route::resource('batches', ProductBatchController::class);
        Route::post('batches/{batch}/certificates', [
            ProductBatchController::class,
            'storeCertificate',
        ])->name('batches.certificates.store');

        Route::get('certificates/{certificate}', [
            CertificateFileController::class,
            'seller',
        ])->name('certificates.show');
    });

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.role');
        Route::patch('/users/{user}/status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.status');
        Route::get('/products', [AdminDashboardController::class, 'products'])->name('products.index');
        Route::patch('/products/{product}/toggle', [AdminDashboardController::class, 'toggleProduct'])->name('products.toggle');
        Route::get('/certificates', [AdminDashboardController::class, 'certificates'])->name('certificates.index');
        Route::patch('/certificates/{certificate}', [AdminDashboardController::class, 'reviewCertificate'])->name('certificates.review');
        Route::get('/certificates/{certificate}/file', [
            CertificateFileController::class,
            'admin',
        ])->name('certificates.file');
        Route::get('/reviews', [AdminDashboardController::class, 'reviews'])->name('reviews.index');
        Route::patch('/reviews/{review}', [AdminDashboardController::class, 'reviewReview'])->name('reviews.review');
    });
