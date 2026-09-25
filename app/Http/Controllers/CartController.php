<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
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
            'variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'variant_id.required' => 'Vui lòng chọn phân loại sản phẩm.',
            'variant_id.exists' => 'Phân loại sản phẩm không hợp lệ.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng phải từ 1 trở lên.',
        ]);

        if (!$product->status) {
            return back()->withErrors([
                'product' => 'Sản phẩm hiện không được bán.',
            ]);
        }

        $variant = $product->variants()
            ->with('unit')
            ->whereKey($validated['variant_id'])
            ->where('status', true)
            ->first();

        if (!$variant) {
            return back()->withErrors([
                'variant_id' => 'Phân loại không thuộc sản phẩm này hoặc đã ngừng bán.',
            ]);
        }

        $quantity = (int) $validated['quantity'];

        if ($variant->stock <= 0) {
            return back()->withErrors([
                'quantity' => 'Phân loại này đã hết hàng.',
            ]);
        }

        if ($quantity > $variant->stock) {
            return back()->withErrors([
                'quantity' => 'Số lượng vượt quá tồn kho của phân loại.',
            ]);
        }

        $cart = session()->get('cart', []);
        $cartKey = (string) $variant->id;

        $currentQuantity = $cart[$cartKey]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $variant->stock) {
            return back()->withErrors([
                'quantity' => 'Tổng số lượng trong giỏ vượt quá tồn kho.',
            ]);
        }

        $cart[$cartKey] = [
            'id' => $product->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'name' => $product->name,
            'variant_name' => $variant->display_name,
            'sku' => $variant->sku,
            'quantity' => $newQuantity,
            'price' => (float) $variant->price,
            'image' => $variant->image
                ?: ($product->image ?? ''),
        ];

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã thêm phân loại sản phẩm vào giỏ hàng!'
        );
    }

    public function update(
        Request $request,
        int $variant
    ): RedirectResponse {
        $validated = $request->validate([
            'action' => [
                'required',
                'in:increase,decrease',
            ],
        ]);

        $cart = session()->get('cart', []);
        $cartKey = (string) $variant;

        if (!isset($cart[$cartKey])) {
            return back()->withErrors([
                'cart' => 'Phân loại không tồn tại trong giỏ hàng.',
            ]);
        }

        if ($validated['action'] === 'increase') {
            $productVariant = ProductVariant::query()
                ->with('product')
                ->find($variant);

            if (
                !$productVariant ||
                !$productVariant->status ||
                !$productVariant->product ||
                !$productVariant->product->status
            ) {
                return back()->withErrors([
                    'cart' => 'Phân loại này hiện không còn được bán.',
                ]);
            }

            if (
                $cart[$cartKey]['quantity'] >=
                $productVariant->stock
            ) {
                return back()->withErrors([
                    'quantity' => 'Số lượng đã đạt mức tồn kho.',
                ]);
            }

            $cart[$cartKey]['quantity']++;
        } else {
            $cart[$cartKey]['quantity']--;

            if ($cart[$cartKey]['quantity'] <= 0) {
                unset($cart[$cartKey]);
            }
        }

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã cập nhật giỏ hàng!'
        );
    }

    public function remove(int $variant): RedirectResponse
    {
        $cart = session()->get('cart', []);
        $cartKey = (string) $variant;

        unset($cart[$cartKey]);

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã xóa phân loại khỏi giỏ hàng!'
        );
    }
}