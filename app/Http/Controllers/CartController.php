<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = session()->get('cart', []);

        return view('cart', compact('cart'));
    }

    public function add(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        if ($quantity > $product->stock) {
            return back()->withErrors([
                'quantity' => 'Số lượng vượt quá tồn kho.',
            ]);
        }

        $cart = session()->get('cart', []);
        $productId = $product->id;

        $currentQuantity = $cart[$productId]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $product->stock) {
            return back()->withErrors([
                'quantity' => 'Tổng số lượng vượt quá tồn kho.',
            ]);
        }

        $cart[$productId] = [
            'id' => $productId,
            'name' => $product->name,
            'quantity' => $newQuantity,
            'price' => $product->price,
            'image' => $product->image ?? '',
        ];

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã thêm sản phẩm vào giỏ hàng!'
        );
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $request->validate([
            'action' => [
                'required',
                'in:increase,decrease',
            ],
        ]);

        $cart = session()->get('cart', []);
        $productId = $product->id;

        if (!isset($cart[$productId])) {
            return back()->withErrors([
                'cart' => 'Sản phẩm không tồn tại trong giỏ hàng.',
            ]);
        }

        if ($validated['action'] === 'increase') {
            if ($cart[$productId]['quantity'] >= $product->stock) {
                return back()->withErrors([
                    'quantity' => 'Số lượng đã đạt mức tồn kho.',
                ]);
            }

            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId]['quantity']--;

            if ($cart[$productId]['quantity'] <= 0) {
                unset($cart[$productId]);
            }
        }

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã cập nhật giỏ hàng!'
        );
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session()->get('cart', []);

        unset($cart[$product->id]);

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã xóa sản phẩm khỏi giỏ hàng!'
        );
    }
}