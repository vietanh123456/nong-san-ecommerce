<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Product;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ (Lấy danh sách sản phẩm từ DB ra)
Route::get('/', function () {
    $products = Product::all();
    return view('home', compact('products'));
})->name('home');

// Route Đăng nhập / Đăng ký / Đăng xuất
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Xử lý Yêu thích (Đưa ra ngoài nhóm auth để Controller tự xử lý kiểm tra đăng nhập)
Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Route Hồ sơ cá nhân & Địa chỉ (Yêu cầu đăng nhập)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    
    // Thêm & Xóa địa chỉ
    Route::post('/address/add', function (Request $request) {
        Address::create([
            'user_id' => Auth::id(),
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'address_detail' => $request->address_detail,
            'is_default' => $request->has('is_default') ? true : false,
        ]);
        return back()->with('success', 'Thêm địa chỉ thành công!');
    });

    Route::delete('/address/delete/{id}', function ($id) {
        Address::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Xóa địa chỉ thành công!');
    });
});

// Route Danh sách sản phẩm
Route::get('/products', [ProductController::class, 'index'])->name('products.index');