@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">

    <h2 style="color: #087443; margin-bottom: 25px;">
        Lịch sử đơn hàng
    </h2>

    @if(session('success'))
        <div style="
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        ">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())

        <div style="
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        ">
            <h3>Bạn chưa có đơn hàng nào</h3>

            <p style="color: #666;">
                Hãy chọn sản phẩm và đặt đơn hàng đầu tiên.
            </p>

            <a href="{{ route('products.index') }}"
               style="
                    display: inline-block;
                    margin-top: 10px;
                    padding: 10px 20px;
                    background: #059669;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
               ">
                Tiếp tục mua sắm
            </a>
        </div>

    @else

        @foreach($orders as $order)

            <div style="
                background: white;
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
            ">

                {{-- HEADER ĐƠN HÀNG --}}
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 15px;
                    margin-bottom: 15px;
                ">

                    <div>
                        <strong>
                            Đơn hàng #{{ $order->id }}
                        </strong>

                        <div style="
                            color: #777;
                            font-size: 14px;
                            margin-top: 5px;
                        ">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div>
                        @switch($order->status)

                            @case('pending')
                                <span style="
                                    background: #fff3cd;
                                    color: #856404;
                                    padding: 6px 12px;
                                    border-radius: 20px;
                                ">
                                    Chờ xử lý
                                </span>
                                @break

                            @case('processing')
                                <span style="
                                    background: #cfe2ff;
                                    color: #084298;
                                    padding: 6px 12px;
                                    border-radius: 20px;
                                ">
                                    Đang xử lý
                                </span>
                                @break

                            @case('shipping')
                                <span style="
                                    background: #cff4fc;
                                    color: #055160;
                                    padding: 6px 12px;
                                    border-radius: 20px;
                                ">
                                    Đang giao hàng
                                </span>
                                @break

                            @case('completed')
                                <span style="
                                    background: #d1e7dd;
                                    color: #0f5132;
                                    padding: 6px 12px;
                                    border-radius: 20px;
                                ">
                                    Hoàn thành
                                </span>
                                @break

                            @case('cancelled')
                                <span style="
                                    background: #f8d7da;
                                    color: #842029;
                                    padding: 6px 12px;
                                    border-radius: 20px;
                                ">
                                    Đã hủy
                                </span>
                                @break

                            @default
                                <span>
                                    {{ $order->status }}
                                </span>

                        @endswitch
                    </div>

                </div>

                {{-- DANH SÁCH SẢN PHẨM --}}
                @foreach($order->orderDetails as $detail)

                    <div style="
                        display: flex;
                        justify-content: space-between;
                        padding: 10px 0;
                    ">

                        <div>

                            <strong>
                                {{ $detail->product->name ?? 'Sản phẩm' }}
                            </strong>

                            <div style="
                                color: #666;
                                margin-top: 5px;
                            ">
                                Số lượng:
                                {{ $detail->quantity }}
                            </div>

                        </div>

                        <div style="text-align: right;">

                            <div>
                                {{ number_format(
                                    (float) $detail->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </div>

                            <strong style="color: #087443;">
                                {{ number_format(
                                    (float) $detail->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </strong>

                        </div>

                    </div>

                @endforeach

                <hr style="
                    border: none;
                    border-top: 1px solid #eee;
                ">

                {{-- FOOTER --}}
                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 15px;
                ">

                    <a
                        href="{{ route('orders.show', $order) }}"
                        style="
                            padding: 9px 16px;
                            border: 1px solid #059669;
                            color: #059669;
                            text-decoration: none;
                            border-radius: 6px;
                        "
                    >
                        Xem chi tiết
                    </a>

                    <div style="text-align: right;">

                        <span style="color: #666;">
                            Tổng thanh toán:
                        </span>

                        <strong style="
                            color: #087443;
                            font-size: 20px;
                            margin-left: 10px;
                        ">
                            {{ number_format(
                                (float) $order->total,
                                0,
                                ',',
                                '.'
                            ) }}đ
                        </strong>

                    </div>

                </div>

            </div>

        @endforeach

    @endif

</div>
@endsection