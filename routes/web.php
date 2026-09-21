<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| TRANG CHỦ & TÌM KIẾM
|--------------------------------------------------------------------------
*/
Route::get('/', function (Request $request) {
    $query = Product::query();

    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where('name', 'LIKE', '%' . $search . '%')
              ->orWhere('description', 'LIKE', '%' . $search . '%');
    }

    $products = $query->latest()->get();
    return view('home', compact('products'));
})->name('home');

/*
|--------------------------------------------------------------------------
| CHI TIẾT SẢN PHẨM
|--------------------------------------------------------------------------
*/
Route::get('/products/{id}', function ($id) {
    $product = Product::findOrFail($id);
    return view('products.show', compact('product'));
})->name('products.show');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

/*
|--------------------------------------------------------------------------
| ĐĂNG NHẬP / ĐĂNG KÝ / ĐĂNG XUẤT (BỔ SUNG ĐỂ HẾT LỖI)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| GIỎ HÀNG (CART)
|--------------------------------------------------------------------------
*/
Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    return view('cart', compact('cart'));
})->name('cart.index');

Route::post('/cart/add/{id}', function (Request $request, $id) {
    $product = Product::findOrFail($id);
    $cart = session()->get('cart', []);
    $quantity = $request->input('quantity', 1);

    if (isset($cart[$id])) {
        $cart[$id]['quantity'] += $quantity;
    } else {
        $cart[$id] = [
            "name" => $product->name,
            "quantity" => $quantity,
            "price" => $product->price,
            "image" => $product->image ?? ''
        ];
    }

    session()->put('cart', $cart);
    return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng!');
})->name('cart.add');

Route::patch('/cart/update/{id}', function (Request $request, $id) {
    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        if ($request->action === 'increase') {
            $cart[$id]['quantity']++;
        } elseif ($request->action === 'decrease') {
            $cart[$id]['quantity']--;
            if ($cart[$id]['quantity'] <= 0) {
                unset($cart[$id]);
            }
        }
        session()->put('cart', $cart);
    }

    return redirect()->back()->with('success', 'Đã cập nhật giỏ hàng!');
})->name('cart.update');

Route::delete('/cart/remove/{id}', function ($id) {
    $cart = session()->get('cart', []);
    if (isset($cart[$id])) {
        unset($cart[$id]);
        session()->put('cart', $cart);
    }
    return redirect()->back()->with('success', 'Đã xóa sản phẩm!');
})->name('cart.remove');

/*
|--------------------------------------------------------------------------
| YÊU THÍCH (WISHLIST)
|--------------------------------------------------------------------------
*/
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle/{productId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/home', function () {
    $products = Product::latest()->get();
    return view('home', compact('products'));
})->name('home');
/*
|--------------------------------------------------------------------------
| THÔNG TIN TÀI KHOẢN (PROFILE)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/address/add', fn () => view('address_add'))->name('address.add');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/seller-request', [ProfileController::class, 'requestSellerRole'])->name('profile.seller-request');
    Route::post('/address', [ProfileController::class, 'storeAddress'])->name('address.store');
    Route::put('/address/{address}', [ProfileController::class, 'updateAddress'])->name('address.update');
    Route::delete('/address/{address}', [ProfileController::class, 'deleteAddress'])->name('address.destroy');
    Route::patch('/address/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('address.default');
});

Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/', [ProductController::class, 'sellerIndex'])->name('dashboard');
    Route::get('/products', [ProductController::class, 'sellerIndex'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'sellerCreate'])->name('products.create');
    Route::post('/products', [ProductController::class, 'sellerStore'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'sellerEdit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'sellerUpdate'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'sellerDestroy'])->name('products.destroy');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'sellerToggle'])->name('products.toggle');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::patch('/sellers/{user}/approve', [AdminController::class, 'approveSeller'])->name('sellers.approve');
    Route::patch('/sellers/{user}/reject', [AdminController::class, 'rejectSeller'])->name('sellers.reject');
    Route::patch('/sellers/{user}/revoke', [AdminController::class, 'revokeSeller'])->name('sellers.revoke');
});