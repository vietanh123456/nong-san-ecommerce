<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sellerId = Auth::id();

        $productQuery = Product::query()
            ->where('seller_id', $sellerId);

        $statistics = [
            'total_products' => (clone $productQuery)->count(),

            'active_products' => (clone $productQuery)
                ->where('status', true)
                ->count(),

            'total_stock' => (clone $productQuery)
                ->sum('stock'),

            'low_stock_products' => (clone $productQuery)
                ->where('stock', '<=', 10)
                ->count(),

            'total_reviews' => Review::query()
                ->whereHas('product', function ($query) use ($sellerId) {
                    $query->where('seller_id', $sellerId);
                })
                ->count(),

            'pending_reviews' => Review::query()
                ->where('status', Review::STATUS_PENDING)
                ->whereHas('product', function ($query) use ($sellerId) {
                    $query->where('seller_id', $sellerId);
                })
                ->count(),
        ];

        $recentProducts = (clone $productQuery)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        $lowStockProducts = (clone $productQuery)
            ->with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('seller.dashboard', compact(
            'statistics',
            'recentProducts',
            'lowStockProducts'
        ));
    }
}