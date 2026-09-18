<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $wishlists = Wishlist::query()
            ->with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('wishlist', compact('wishlists'));
    }

    public function toggle(Product $product): RedirectResponse
    {
        $wishlist = Wishlist::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist !== null) {
            $wishlist->delete();

            return back()->with(
                'success',
                'Đã xóa khỏi danh sách yêu thích!'
            );
        }

        Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        return back()->with(
            'success',
            'Đã thêm vào danh sách yêu thích!'
        );
    }
}