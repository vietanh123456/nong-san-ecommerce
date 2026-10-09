<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'sort' => ['nullable', 'in:price_asc,price_desc'],
        ]);

        if (
            isset($filters['min_price'], $filters['max_price'])
            && $filters['max_price'] < $filters['min_price']
        ) {
            throw ValidationException::withMessages([
                'max_price' => 'Giá đến phải lớn hơn hoặc bằng giá từ.',
            ]);
        }

        $query = Product::query()
            ->with('category')
            ->where('status', true);

        if (filled($filters['search'] ?? null)) {
            $query->search($filters['search']);
        }

        $categories = Category::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();
        $categoriesByParent = $categories->groupBy('parent_id');
        $buildCategoryTree = function ($parentId) use (&$buildCategoryTree, $categoriesByParent) {
            return $categoriesByParent->get($parentId, collect())
                ->map(function (Category $category) use (&$buildCategoryTree) {
                    $category->setRelation('children', $buildCategoryTree($category->id));

                    return $category;
                });
        };
        $categoryTree = $buildCategoryTree(null);

        $selectedCategoryIds = collect($filters['categories'] ?? [])
            ->when(
                filled($filters['category_id'] ?? null),
                fn ($ids) => $ids->push((int) $filters['category_id'])
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedCategoryIds->isNotEmpty()) {
            $includeDescendants = function (int $categoryId) use (&$includeDescendants, $categoriesByParent) {
                return collect([$categoryId])->merge(
                    $categoriesByParent
                        ->get($categoryId, collect())
                        ->flatMap(fn (Category $child) => $includeDescendants($child->id))
                );
            };
            $categoryIds = $selectedCategoryIds
                ->flatMap(fn (int $categoryId) => $includeDescendants($categoryId))
                ->unique();

            $query->whereIn('category_id', $categoryIds);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['rating'])) {
            $rating = (int) $filters['rating'];

            $query->whereIn(
                'id',
                Review::query()
                    ->approved()
                    ->select('product_id')
                    ->groupBy('product_id')
                    ->havingRaw('AVG(rating) >= ?', [$rating])
            );
        }

        if (($filters['sort'] ?? null) === 'price_asc') {
            $query->orderBy('price');
        } elseif (($filters['sort'] ?? null) === 'price_desc') {
            $query->orderByDesc('price');
        } else {
            $query->latest();
        }

        $products = $query
            ->paginate(8)
            ->withQueryString();

        return view(
            'products.index',
            compact('products', 'categoryTree', 'selectedCategoryIds')
        );
    }

    public function show(int $id): View
    {
        $product = Product::query()
            ->with([
                'category',
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
            ? round((float) $product->reviews->avg('rating'), 1)
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
