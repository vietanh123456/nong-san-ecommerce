<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sellerId = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM CỦA SELLER
        |--------------------------------------------------------------------------
        */

        $productQuery = Product::query()
            ->where('seller_id', $sellerId);

        /*
        |--------------------------------------------------------------------------
        | CHI TIẾT ĐƠN HÀNG CÓ SẢN PHẨM CỦA SELLER
        |--------------------------------------------------------------------------
        */

        $sellerOrderDetails = OrderDetail::query()
            ->whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            });

        /*
        |--------------------------------------------------------------------------
        | THỐNG KÊ
        |--------------------------------------------------------------------------
        */

        $statistics = [

            // =========================
            // SẢN PHẨM
            // =========================

            'total_products' => (clone $productQuery)->count(),

            'active_products' => (clone $productQuery)
                ->where('status', true)
                ->count(),

            'total_stock' => (clone $productQuery)
                ->sum('stock'),

            'low_stock_products' => (clone $productQuery)
                ->where('stock', '<=', 10)
                ->count(),

            // =========================
            // REVIEW
            // =========================

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

            // =========================
            // ĐƠN HÀNG
            // =========================

            'total_orders' => (clone $sellerOrderDetails)
                ->distinct()
                ->count('order_id'),

            'pending_orders' => (clone $sellerOrderDetails)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'pending');
                })
                ->distinct()
                ->count('order_id'),

            'processing_orders' => (clone $sellerOrderDetails)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'processing');
                })
                ->distinct()
                ->count('order_id'),

            'shipping_orders' => (clone $sellerOrderDetails)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'shipping');
                })
                ->distinct()
                ->count('order_id'),

            'completed_orders' => (clone $sellerOrderDetails)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'completed');
                })
                ->distinct()
                ->count('order_id'),

            // =========================
            // DOANH THU
            // =========================

            'total_revenue' => (clone $sellerOrderDetails)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'completed');
                })
                ->sum('subtotal'),
        ];

        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM MỚI
        |--------------------------------------------------------------------------
        */

        $recentProducts = (clone $productQuery)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SẢN PHẨM SẮP HẾT
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = (clone $productQuery)
            ->with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TRẢ VỀ DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view('seller.dashboard', compact(
            'statistics',
            'recentProducts',
            'lowStockProducts'
        ));
    }
}