<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Hiển thị danh sách yêu thích của User đang đăng nhập
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Vui lòng đăng nhập để xem danh sách yêu thích!');
        }

        // Lấy danh sách kèm thông tin sản phẩm
        $wishlists = Wishlist::with('product')->where('user_id', Auth::id())->latest()->get();
        return view('wishlist', compact('wishlists'));
    }

    // Thêm hoặc Xóa sản phẩm khỏi Yêu thích (Toggle)
    public function toggle($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Vui lòng đăng nhập để lưu sản phẩm yêu thích!');
        }

        $wishlist = Wishlist::where('user_id', Auth::id())
                            ->where('product_id', $productId)
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return back()->with('success', 'Đã xóa khỏi danh sách yêu thích!');
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
            ]);
            return back()->with('success', 'Đã thêm vào danh sách yêu thích!');
        }
    }
}