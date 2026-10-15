<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Lịch sử đơn hàng của khách hàng.
     */
    public function index(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with([
                'user',
                'address',
                'orderDetails.product',
            ])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng.
     */
    public function show(Order $order): View
    {
        abort_unless(
            (int) $order->user_id === (int) auth()->id(),
            403,
            'Bạn không có quyền xem đơn hàng này.'
        );

        $order->load([
            'user',
            'address',
            'orderDetails.product',
        ]);

        return view('orders.show', compact('order'));
    }

    /**
     * Khách hàng xác nhận đã nhận được hàng.
     */
    public function confirmReceived(Order $order): RedirectResponse
    {
        abort_unless(
            (int) $order->user_id === (int) auth()->id(),
            403,
            'Bạn không có quyền xác nhận đơn hàng này.'
        );

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Không thay đổi thời gian nếu đã xác nhận trước đó.
            if ($lockedOrder->received_at !== null) {
                return;
            }

            abort_unless(
                in_array(
                    $lockedOrder->status,
                    [
                        Order::STATUS_SHIPPING,
                        Order::STATUS_COMPLETED,
                    ],
                    true
                ),
                422,
                'Chỉ có thể xác nhận khi đơn hàng đang giao hoặc đã hoàn thành.'
            );

            $lockedOrder->received_at = now();
            $lockedOrder->status = Order::STATUS_COMPLETED;

            // Xác nhận nhận hàng COD đồng thời ghi nhận đã thanh toán.
            if ($lockedOrder->payment_method === 'cod') {
                $lockedOrder->payment_status = Order::PAYMENT_STATUS_PAID;
            }

            $lockedOrder->save();
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Bạn đã xác nhận nhận hàng thành công.');
    }
}