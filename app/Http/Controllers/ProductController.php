<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with('category')
            ->where('status', true);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        if ($request->input('sort') === 'price_asc') {
            $query->orderBy('price');
        } elseif ($request->input('sort') === 'price_desc') {
            $query->orderByDesc('price');
        } else {
            $query->latest();
        }

        $products = $query
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view(
            'products.index',
            compact('products', 'categories')
        );
    }

    public function show(int $id): View
    {
        $product = Product::query()
            ->with([
                'category',

                'variants' => function ($query): void {
                    $query
                        ->where('status', true)
                        ->with('unit')
                        ->orderBy('price');
                },

                'reviews' => function ($query): void {
                    $query
                        ->approved()
                        ->with('user')
                        ->latest();
                },
            ])
            ->where('status', true)
            ->findOrFail($id);

        $reviewCount = $product->reviews->count();

        $averageRating = $reviewCount > 0
            ? round(
                (float) $product->reviews->avg('rating'),
                1
            )
            : 0;

        return view(
            'products.show',
            compact(
                'product',
                'reviewCount',
                'averageRating'
            )
        );
    }
}