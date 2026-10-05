<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    /**
     * Trang báo cáo doanh thu & tổng quan trong khu vực Admin.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $today = Carbon::today();

        $revenue = [
            'total' => $this->sumRevenue(),
            'today' => $this->sumRevenue($today->copy()->startOfDay(), $today->copy()->endOfDay()),
            'last_7_days' => $this->sumRevenue($today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()),
            'current_month' => $this->sumRevenue($today->copy()->startOfMonth(), $today->copy()->endOfMonth()),
        ];

        $fromDate = isset($validated['from_date']) ? Carbon::parse($validated['from_date'])->startOfDay() : null;
        $toDate = isset($validated['to_date']) ? Carbon::parse($validated['to_date'])->endOfDay() : null;

        $customRange = null;

        if ($fromDate || $toDate) {
            $customRange = [
                'from_date' => $fromDate?->toDateString(),
                'to_date' => $toDate?->toDateString(),
                'revenue' => $this->sumRevenue($fromDate, $toDate),
            ];
        }

        $ordersByStatus = [
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'shipping' => Order::where('status', Order::STATUS_SHIPPING)->count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];

        $totalOrders = array_sum($ordersByStatus);

        $topProductsByRevenue = $this->topProducts('revenue');
        $topProductsByQuantity = $this->topProducts('quantity');

        $totalCustomers = User::where('role', 'customer')->count();

        return view('admin.statistics', [
            'revenue' => $revenue,
            'customRange' => $customRange,
            'filters' => [
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
            ],
            'ordersByStatus' => $ordersByStatus,
            'totalOrders' => $totalOrders,
            'topProductsByRevenue' => $topProductsByRevenue,
            'topProductsByQuantity' => $topProductsByQuantity,
            'totalCustomers' => $totalCustomers,
        ]);
    }

    /**
     * Thống kê doanh thu: tổng tích lũy, theo mốc thời gian và theo khoảng ngày tùy biến.
     */
    public function revenue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $today = Carbon::today();

        $totalRevenue = $this->sumRevenue();

        $todayRevenue = $this->sumRevenue(
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay()
        );

        $last7DaysRevenue = $this->sumRevenue(
            $today->copy()->subDays(6)->startOfDay(),
            $today->copy()->endOfDay()
        );

        $currentMonthRevenue = $this->sumRevenue(
            $today->copy()->startOfMonth(),
            $today->copy()->endOfMonth()
        );

        $customRange = null;

        if (! empty($validated['from_date']) || ! empty($validated['to_date'])) {
            $fromDate = isset($validated['from_date'])
                ? Carbon::parse($validated['from_date'])->startOfDay()
                : null;

            $toDate = isset($validated['to_date'])
                ? Carbon::parse($validated['to_date'])->endOfDay()
                : null;

            $customRange = [
                'from_date' => $fromDate?->toDateString(),
                'to_date' => $toDate?->toDateString(),
                'revenue' => $this->sumRevenue($fromDate, $toDate),
            ];
        }

        return response()->json([
            'data' => [
                'total_revenue' => $totalRevenue,
                'today_revenue' => $todayRevenue,
                'last_7_days_revenue' => $last7DaysRevenue,
                'current_month_revenue' => $currentMonthRevenue,
                'custom_range_revenue' => $customRange,
            ],
        ]);
    }

    /**
     * Tổng quan: số đơn theo trạng thái, top sản phẩm bán chạy, tổng khách hàng.
     */
    public function overview(): JsonResponse
    {
        $ordersByStatus = [
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'shipping' => Order::where('status', Order::STATUS_SHIPPING)->count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];

        $totalOrders = array_sum($ordersByStatus);

        $topProductsByRevenue = $this->topProducts('revenue');
        $topProductsByQuantity = $this->topProducts('quantity');

        $totalCustomers = User::where('role', 'customer')->count();

        return response()->json([
            'data' => [
                'total_orders' => $totalOrders,
                'orders_by_status' => $ordersByStatus,
                'top_products_by_revenue' => $topProductsByRevenue,
                'top_products_by_quantity' => $topProductsByQuantity,
                'total_customers' => $totalCustomers,
            ],
        ]);
    }

    /**
     * Tính tổng doanh thu từ các đơn hàng đã hoàn thành, có thể lọc theo khoảng thời gian.
     */
    private function sumRevenue(?Carbon $from = null, ?Carbon $to = null): float
    {
        return (float) Order::query()
            ->revenueCountable()
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to))
            ->sum('total');
    }

    /**
     * Lấy top 5 sản phẩm bán chạy theo doanh thu hoặc theo số lượng (chỉ tính đơn đã hoàn thành).
     */
    private function topProducts(string $orderBy): array
    {
        $column = $orderBy === 'quantity' ? 'total_quantity' : 'total_revenue';

        $results = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->groupBy('products.id', 'products.name')
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.subtotal) as total_revenue'),
            ])
            ->orderByDesc($column)
            ->limit(5)
            ->get();

        return $results->map(fn ($row) => [
            'product_id' => $row->product_id,
            'product_name' => $row->product_name,
            'total_quantity' => (int) $row->total_quantity,
            'total_revenue' => (float) $row->total_revenue,
        ])->all();
    }
}
