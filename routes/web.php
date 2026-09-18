<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;


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
Route::get('/profile', function () {
    return view('profile');
})->name('profile')->middleware('auth');
// Route hiển thị trang thêm địa chỉ
Route::get('/address/add', function () {
    return view('address_add');
})->middleware('auth')->name('address.add');

// Route xử lý lưu địa chỉ
Route::post('/address/add', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'phone' => 'required',
        'address' => 'required',
    ]);

    $user = Auth::user();
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->save();

    return redirect('/profile')->with('success', 'Cập nhật địa chỉ thành công!');
})->middleware('auth')->name('address.store');
// Route hiển thị trang thêm địa chỉ
Route::get('/address/add', function () {
    return view('address_add');
})->middleware('auth')->name('address.add');

// Route xử lý lưu địa chỉ
Route::post('/address/add', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'phone' => 'required',
        'address' => 'required',
    ]);

    $user = Auth::user();
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->save();

    return redirect('/profile')->with('success', 'Cập nhật địa chỉ thành công!');
})->middleware('auth')->name('address.store');