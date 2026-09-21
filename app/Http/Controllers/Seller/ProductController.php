<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreProductRequest;
use App\Http\Requests\Seller\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with(['category', 'variants.unit'])
            ->where('seller_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('seller.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'seller.products.create',
            compact('categories', 'units')
        );
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $variants = $data['variants'];

        unset($data['variants'], $data['image']);

        $data['seller_id'] = Auth::id();
        $data['price'] = collect($variants)->min('price');
        $data['stock'] = collect($variants)->sum('stock');
        $data['status'] = $request->boolean('status');

        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')
                    ->store('products', 'public');

                $data['image'] = $imagePath;
            }

            DB::transaction(function () use ($data, $variants): void {
                $product = Product::create($data);

                foreach ($variants as $variant) {
                    $product->variants()->create([
                        'name' => $variant['name'],
                        'unit_id' => $variant['unit_id'] ?? null,
                        'sku' => $variant['sku'],
                        'quantity' => $variant['quantity'] ?? null,
                        'price' => $variant['price'],
                        'stock' => $variant['stock'],
                        'status' => (bool) (
                            $variant['status'] ?? true
                        ),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Thêm sản phẩm thành công.');
    }

    public function show(Product $product): View
    {
        $this->ensureProductBelongsToSeller($product);

        $product->load([
            'category',
            'variants.unit',
            'reviews.user',
        ]);

        return view('seller.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->ensureProductBelongsToSeller($product);

        $product->load('variants.unit');

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'seller.products.edit',
            compact('product', 'categories', 'units')
        );
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {
        $this->ensureProductBelongsToSeller($product);

        $data = $request->validated();
        $variants = $data['variants'];

        unset($data['variants'], $data['image']);

        $data['price'] = collect($variants)->min('price');
        $data['stock'] = collect($variants)->sum('stock');
        $data['status'] = $request->boolean('status');

        $oldImage = $product->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')
                    ->store('products', 'public');

                $data['image'] = $newImage;
            }

            DB::transaction(function () use (
                $product,
                $data,
                $variants
            ): void {
                $product->update($data);

                $keptVariantIds = [];

                foreach ($variants as $variantData) {
                    $variantId = $variantData['id'] ?? null;

                    $values = [
                        'name' => $variantData['name'],
                        'unit_id' => $variantData['unit_id'] ?? null,
                        'sku' => $variantData['sku'],
                        'quantity' => $variantData['quantity'] ?? null,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'status' => (bool) (
                            $variantData['status'] ?? true
                        ),
                    ];

                    if ($variantId !== null) {
                        $variant = $product->variants()
                            ->findOrFail($variantId);

                        $variant->update($values);
                    } else {
                        $variant = $product->variants()
                            ->create($values);
                    }

                    $keptVariantIds[] = $variant->id;
                }

                $product->variants()
                    ->whereNotIn('id', $keptVariantIds)
                    ->delete();
            });
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->ensureProductBelongsToSeller($product);

        $image = $product->image;

        $product->delete();

        if ($image !== null) {
            Storage::disk('public')->delete($image);
        }

        return redirect()
            ->route('seller.products.index')
            ->with('success', 'Xóa sản phẩm thành công.');
    }

    private function ensureProductBelongsToSeller(Product $product): void
    {
        abort_unless(
            $product->seller_id === Auth::id(),
            403,
            'Bạn không có quyền quản lý sản phẩm này.'
        );
    }
}