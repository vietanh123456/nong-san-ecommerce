<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Lịch sử đơn hàng của khách hàng
     */
    public function index(Request $request): View
    {
        $orders = Order::where('user_id', auth()->id())
            ->with([
                'address',
                'orderDetails.product',
            ])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Chi tiết một đơn hàng
     */
    public function show(Order $order): View
    {
        // Không cho người dùng xem đơn của người khác
        abort_if(
            $order->user_id !== auth()->id(),
            403,
            'Bạn không có quyền xem đơn hàng này.'
        );

        $order->load([
            'address',
            'orderDetails.product',
        ]);

        return view('orders.show', compact('order'));
    }
}