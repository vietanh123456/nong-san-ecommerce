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
        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');
        $cartHasUnavailableProducts = false;

        foreach ($cart as $productId => &$item) {
            $product = $products->get($productId);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
            $item['availability_error'] = null;

            if (! $product) {
                $item['availability_error'] = 'Sản phẩm không còn tồn tại.';
            } elseif (! $product->status) {
                $item['availability_error'] = 'Sản phẩm hiện không còn được bán.';
            } elseif ($quantity === false || $quantity < 1) {
                $item['availability_error'] = 'Số lượng sản phẩm không hợp lệ.';
            } elseif ($quantity > $product->stock) {
                $item['availability_error'] = 'Số lượng trong giỏ vượt quá tồn kho hiện tại.';
            } else {
                $item['availability_error'] = null;
            }

            if ($product) {
                $item['name'] = $product->name;
                $item['price'] = $product->price;
                $item['image'] = $product->image ?? '';
            } else {
                $item['price'] = 0;
            }

            $cartHasUnavailableProducts = $cartHasUnavailableProducts
                || $item['availability_error'] !== null;
        }
        unset($item);

        session()->put('cart', $cart);

        return view('cart', compact('cart', 'cartHasUnavailableProducts'));
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

        if (! $product->status) {
            return back()->withErrors([
                'quantity' => 'Sản phẩm hiện không còn được bán.',
            ]);
        }

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
        int $productId
    ): RedirectResponse {
        $validated = $request->validate([
            'action' => [
                'required',
                'in:increase,decrease',
            ],
        ]);

        $cart = session()->get('cart', []);

        if (! isset($cart[$productId])) {
            return back()->withErrors([
                'cart' => 'Sản phẩm không tồn tại trong giỏ hàng.',
            ]);
        }

        if ($validated['action'] === 'increase') {
            $product = Product::query()->find($productId);

            if (! $product || ! $product->status) {
                return back()->withErrors([
                    'cart' => 'Sản phẩm hiện không còn được bán.',
                ]);
            }

            $currentQuantity = filter_var(
                $cart[$productId]['quantity'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($currentQuantity === false || $currentQuantity < 1) {
                return back()->withErrors([
                    'quantity' => 'Số lượng sản phẩm trong giỏ không hợp lệ.',
                ]);
            }

            if ($currentQuantity >= $product->stock) {
                return back()->withErrors([
                    'quantity' => 'Số lượng đã đạt mức tồn kho.',
                ]);
            }

            $cart[$productId]['quantity'] = $currentQuantity + 1;
            $cart[$productId]['name'] = $product->name;
            $cart[$productId]['price'] = $product->price;
            $cart[$productId]['image'] = $product->image ?? '';
        } else {
            $currentQuantity = filter_var(
                $cart[$productId]['quantity'] ?? null,
                FILTER_VALIDATE_INT
            );
            $cart[$productId]['quantity'] = max(0, (int) $currentQuantity - 1);

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

    public function remove(int $productId): RedirectResponse
    {
        $cart = session()->get('cart', []);

        unset($cart[$productId]);

        session()->put('cart', $cart);

        return back()->with(
            'success',
            'Đã xóa sản phẩm khỏi giỏ hàng!'
        );
    }
}
