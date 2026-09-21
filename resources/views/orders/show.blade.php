@extends('layouts.app')

@section('content')
<div style="max-width: 900px; margin: 30px auto; padding: 0 20px;">

    <div style="margin-bottom: 20px;">
        <a href="{{ route('orders.index') }}"
           style="color: #059669; text-decoration: none;">
            ← Quay lại lịch sử đơn hàng
        </a>
    </div>

    <h2 style="color: #087443; margin-bottom: 25px;">
        Chi tiết đơn hàng #{{ $order->id }}
    </h2>

    {{-- THÔNG TIN ĐƠN --}}
    <div style="
        background: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    ">

        <h3 style="margin-top: 0;">
            Thông tin đơn hàng
        </h3>

        <p>
            <strong>Mã đơn:</strong>
            #{{ $order->id }}
        </p>

        <p>
            <strong>Ngày đặt:</strong>
            {{ $order->created_at->format('d/m/Y H:i') }}
        </p>

        <p>
            <strong>Trạng thái:</strong>

            @switch($order->status)

                @case('pending')
                    <span style="color: #b7791f;">
                        Chờ xử lý
                    </span>
                    @break

                @case('processing')
                    <span style="color: #2563eb;">
                        Đang xử lý
                    </span>
                    @break

                @case('shipping')
                    <span style="color: #0891b2;">
                        Đang giao hàng
                    </span>
                    @break

                @case('completed')
                    <span style="color: #059669;">
                        Hoàn thành
                    </span>
                    @break

                @case('cancelled')
                    <span style="color: #dc2626;">
                        Đã hủy
                    </span>
                    @break

                @default
                    {{ $order->status }}

            @endswitch
        </p>

        <p>
            <strong>Phương thức thanh toán:</strong>

            @if($order->payment_method === 'cod')
                Thanh toán khi nhận hàng (COD)
            @else
                {{ strtoupper($order->payment_method) }}
            @endif
        </p>

        <p>
            <strong>Trạng thái thanh toán:</strong>

            @if($order->payment_status === 'paid')
                <span style="color: #059669;">
                    Đã thanh toán
                </span>
            @else
                <span style="color: #dc2626;">
                    Chưa thanh toán
                </span>
            @endif
        </p>

    </div>

    {{-- ĐỊA CHỈ NHẬN HÀNG --}}
    <div style="
        background: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    ">

        <h3 style="margin-top: 0;">
            Địa chỉ nhận hàng
        </h3>

        @if($order->address)

            <p>
                <strong>Người nhận:</strong>
                {{ $order->address->receiver_name ?? $order->address->name ?? 'Không có' }}
            </p>

            <p>
                <strong>Số điện thoại:</strong>
                {{ $order->address->phone ?? 'Không có' }}
            </p>

            <p>
                <strong>Địa chỉ:</strong>

                {{ $order->address->address_line ?? $order->address->address ?? '' }}

                {{ $order->address->ward ?? '' }}

                {{ $order->address->district ?? '' }}

                {{ $order->address->province ?? '' }}
            </p>

        @else

            <p>Không tìm thấy thông tin địa chỉ.</p>

        @endif

    </div>

    {{-- SẢN PHẨM --}}
    <div style="
        background: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    ">

        <h3 style="margin-top: 0;">
            Sản phẩm
        </h3>

        @foreach($order->orderDetails as $detail)

            <div style="
                display: flex;
                justify-content: space-between;
                border-bottom: 1px solid #eee;
                padding: 15px 0;
            ">

                <div>

                    <strong>
                        {{ $detail->product->name ?? 'Sản phẩm không còn tồn tại' }}
                    </strong>

                    <div style="
                        color: #666;
                        margin-top: 5px;
                    ">
                        Số lượng:
                        {{ $detail->quantity }}
                    </div>

                    <div style="
                        color: #666;
                        margin-top: 5px;
                    ">
                        Đơn giá:
                        {{ number_format(
                            (float) $detail->price,
                            0,
                            ',',
                            '.'
                        ) }}đ
                    </div>

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

        @endforeach

    </div>

    {{-- THANH TOÁN --}}
    <div style="
        background: #f7f7f7;
        border-radius: 10px;
        padding: 20px;
    ">

        <h3 style="margin-top: 0;">
            Thông tin thanh toán
        </h3>

        <div style="
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        ">
            <span>Tạm tính:</span>

            <strong>
                {{ number_format(
                    (float) $order->subtotal,
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>
        </div>

        <div style="
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        ">
            <span>Phí vận chuyển:</span>

            <strong>
                {{ number_format(
                    (float) $order->shipping_fee,
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>
        </div>

        <div style="
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        ">
            <span>Giảm giá:</span>

            <strong>
                -{{ number_format(
                    (float) $order->discount,
                    0,
                    ',',
                    '.'
                ) }}đ
            </strong>
        </div>

        <hr>

        <div style="
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            color: #087443;
        ">

            <strong>Tổng thanh toán:</strong>

            <strong>
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
@endsection