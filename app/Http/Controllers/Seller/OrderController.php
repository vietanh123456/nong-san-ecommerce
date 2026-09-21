<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng có sản phẩm của seller hiện tại.
     */
    public function index(): View
    {
        $sellerId = auth()->id();

        $orders = Order::query()
            ->whereHas('orderDetails.product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->with([
                'user',
                'address',
                'orderDetails' => function ($query) use ($sellerId) {
                    $query->whereHas('product', function ($productQuery) use ($sellerId) {
                        $productQuery->where('seller_id', $sellerId);
                    });
                },
                'orderDetails.product',
            ])
            ->latest()
            ->get();

        return view('seller.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show(Order $order): View
    {
        $sellerId = auth()->id();

        $hasProduct = $order->orderDetails()
            ->whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->exists();

        abort_unless(
            $hasProduct,
            403,
            'Bạn không có quyền xem đơn hàng này.'
        );

        $order->load([
            'user',
            'address',
            'orderDetails' => function ($query) use ($sellerId) {
                $query->whereHas('product', function ($productQuery) use ($sellerId) {
                    $productQuery->where('seller_id', $sellerId);
                });
            },
            'orderDetails.product',
        ]);

        return view('seller.orders.show', compact('order'));
    }

    /**
     * Seller cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(
        Request $request,
        Order $order
    ): RedirectResponse {
        $sellerId = auth()->id();

        $hasProduct = $order->orderDetails()
            ->whereHas('product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->exists();

        abort_unless(
            $hasProduct,
            403,
            'Bạn không có quyền cập nhật đơn hàng này.'
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,processing,shipping,completed,cancelled',
            ],
        ]);

        $order->status = $validated['status'];

        /*
         * Nếu COD và đơn hoàn thành thì xem như đã thanh toán.
         */
        if (
            $validated['status'] === 'completed'
            && $order->payment_method === 'cod'
        ) {
            $order->payment_status = 'paid';
        }

        $order->save();

        return back()->with(
            'success',
            'Cập nhật trạng thái đơn hàng thành công.'
        );
    }
}