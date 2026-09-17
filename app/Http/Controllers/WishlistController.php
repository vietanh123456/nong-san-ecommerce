<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // 1. Xem danh sách yêu thích
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())->with('product')->get();
        return view('wishlist', compact('wishlists'));
    }

    // 2. Thêm hoặc Bỏ yêu thích
    public function toggle($productId)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để yêu thích sản phẩm!');
        }

        $userId = Auth::id();
        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($wishlist) {
            $wishlist->delete();
            return back()->with('success', 'Đã xóa khỏi danh sách yêu thích!');
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            return back()->with('success', 'Đã thêm vào danh sách yêu thích!');
        }
    }
}