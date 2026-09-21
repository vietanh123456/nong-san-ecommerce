@extends('layouts.app')

@section('content')
<div style="max-width: 1000px; margin: 30px auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Quản lý đơn hàng</h2>

        <a href="{{ route('seller.dashboard') }}"
           style="color:#059669; text-decoration:none;">
            ← Quay lại Dashboard
        </a>
    </div>

    @if(session('success'))
        <div style="
            padding:12px 15px;
            background:#d1fae5;
            color:#065f46;
            border-radius:8px;
            margin-bottom:20px;
        ">
            {{ session('success') }}
        </div>
    @endif

    @if($orders->isEmpty())

        <div style="
            padding:30px;
            background:white;
            border:1px solid #ddd;
            border-radius:10px;
            text-align:center;
        ">
            Chưa có đơn hàng nào chứa sản phẩm của bạn.
        </div>

    @else

        @foreach($orders as $order)

            <div style="
                background:white;
                border:1px solid #ddd;
                border-radius:10px;
                padding:20px;
                margin-bottom:20px;
            ">

                <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    border-bottom:1px solid #eee;
                    padding-bottom:15px;
                    margin-bottom:15px;
                ">

                    <div>
                        <strong>Đơn hàng #{{ $order->id }}</strong>

                        <div style="color:#777; margin-top:5px;">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div>
                        @switch($order->status)

                            @case('pending')
                                <span style="color:#b7791f;">
                                    Chờ xử lý
                                </span>
                                @break

                            @case('processing')
                                <span style="color:#2563eb;">
                                    Đang xử lý
                                </span>
                                @break

                            @case('shipping')
                                <span style="color:#7c3aed;">
                                    Đang giao hàng
                                </span>
                                @break

                            @case('completed')
                                <span style="color:#059669;">
                                    Hoàn thành
                                </span>
                                @break

                            @case('cancelled')
                                <span style="color:#dc2626;">
                                    Đã hủy
                                </span>
                                @break

                            @default
                                {{ $order->status }}

                        @endswitch
                    </div>

                </div>


                {{-- SẢN PHẨM CỦA SELLER TRONG ĐƠN --}}

                @foreach($order->orderDetails as $detail)

                    <div style="
                        display:flex;
                        justify-content:space-between;
                        padding:10px 0;
                    ">

                        <div>
                            <strong>
                                {{ $detail->product->name ?? 'Sản phẩm không tồn tại' }}
                            </strong>

                            <div style="color:#777;">
                                Số lượng: {{ $detail->quantity }}
                            </div>
                        </div>

                        <div>
                            {{ number_format($detail->subtotal, 0, ',', '.') }}đ
                        </div>

                    </div>

                @endforeach


                <div style="
                    border-top:1px solid #eee;
                    margin-top:10px;
                    padding-top:15px;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                ">

                    <div>
                        <strong>
                            Khách hàng:
                        </strong>

                        {{ $order->user->name ?? 'Không có' }}
                    </div>

                    <a
                        href="{{ route('seller.orders.show', $order) }}"
                        style="
                            padding:8px 15px;
                            border:1px solid #059669;
                            border-radius:6px;
                            color:#059669;
                            text-decoration:none;
                        "
                    >
                        Xem chi tiết
                    </a>

                </div>

            </div>

        @endforeach

    @endif

</div>
@endsection