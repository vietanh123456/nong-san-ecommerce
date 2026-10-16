@extends('layouts.app')

@section('content')
<div style="max-width: 900px; margin: 30px auto;">

    <a href="{{ route('seller.orders.index') }}"
       style="color:#059669; text-decoration:none;">
        ← Quay lại danh sách đơn hàng
    </a>

    <h2 style="margin:20px 0;">
        Chi tiết đơn hàng #{{ $order->id }}
    </h2>


    @if(session('success'))
        <div style="
            padding:12px;
            background:#d1fae5;
            color:#065f46;
            border-radius:8px;
            margin-bottom:20px;
        ">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div style="
            padding:12px;
            background:#fee2e2;
            color:#991b1b;
            border-radius:8px;
            margin-bottom:20px;
        ">
            {{ $errors->first() }}
        </div>
    @endif


    {{-- THÔNG TIN ĐƠN --}}

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:10px;
        padding:20px;
        margin-bottom:20px;
    ">

        <h3>Thông tin đơn hàng</h3>

        <p>
            <strong>Mã đơn:</strong>
            #{{ $order->id }}
        </p>

        <p>
            <strong>Ngày đặt:</strong>
            {{ $order->created_at->format('d/m/Y H:i') }}
        </p>

        <p>
            <strong>Khách hàng:</strong>
            {{ $order->user->name ?? 'Không có' }}
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
            <strong>Thanh toán:</strong>

            @if($order->payment_status === 'paid')
                <span style="color:#059669;">
                    Đã thanh toán
                </span>
            @else
                <span style="color:#dc2626;">
                    Chưa thanh toán
                </span>
            @endif
        </p>

    </div>


    {{-- ĐỊA CHỈ --}}

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:10px;
        padding:20px;
        margin-bottom:20px;
    ">

        <h3>Địa chỉ nhận hàng</h3>

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
                {{ $order->address->address ?? $order->address->full_address ?? 'Không có' }}
            </p>

        @else

            <p>Không có thông tin địa chỉ.</p>

        @endif

    </div>


    {{-- SẢN PHẨM --}}

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:10px;
        padding:20px;
        margin-bottom:20px;
    ">

        <h3>Sản phẩm của bạn</h3>

        @foreach($order->orderDetails as $detail)

            <div style="
                display:flex;
                justify-content:space-between;
                padding:15px 0;
                border-bottom:1px solid #eee;
            ">

                <div>

                    <strong>
                        {{ $detail->product->name ?? 'Sản phẩm không tồn tại' }}
                    </strong>

                    <div style="color:#777; margin-top:5px;">
                        Số lượng: {{ $detail->quantity }}
                    </div>

                    <div style="color:#777;">
                        Đơn giá:
                        {{ number_format($detail->price, 0, ',', '.') }}đ
                    </div>

                </div>

                <strong style="color:#059669;">
                    {{ number_format($detail->subtotal, 0, ',', '.') }}đ
                </strong>

            </div>

        @endforeach

    </div>


    {{-- CẬP NHẬT TRẠNG THÁI --}}

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:10px;
        padding:20px;
    ">

        <h3>Xử lý đơn hàng</h3>

        <p>
            Trạng thái hiện tại:

            <strong>
                @switch($order->status)

                    @case('pending')
                        Chờ xử lý
                        @break

                    @case('processing')
                        Đang xử lý
                        @break

                    @case('shipping')
                        Đang giao hàng
                        @break

                    @case('completed')
                        Hoàn thành
                        @break

                    @case('cancelled')
                        Đã hủy
                        @break

                    @default
                        {{ $order->status }}

                @endswitch
            </strong>
        </p>


        <form
            action="{{ route('seller.orders.status', $order) }}"
            method="POST"
        >

            @csrf
            @method('PATCH')

            <label>
                <strong>Chọn trạng thái:</strong>
            </label>

            <select
                name="status"
                required
                style="
                    width:100%;
                    padding:10px;
                    margin:10px 0 15px;
                    border:1px solid #ccc;
                    border-radius:6px;
                "
            >

                <option
                    value="pending"
                    @selected($order->status === 'pending')
                >
                    Chờ xử lý
                </option>

                <option
                    value="processing"
                    @selected($order->status === 'processing')
                >
                    Đang xử lý
                </option>

                <option
                    value="shipping"
                    @selected($order->status === 'shipping')
                >
                    Đang giao hàng
                </option>

                <option
                    value="completed"
                    @selected($order->status === 'completed')
                >
                    Hoàn thành
                </option>

                <option
                    value="cancelled"
                    @selected($order->status === 'cancelled')
                >
                    Hủy đơn
                </option>

            </select>

            <button
                type="submit"
                style="
                    background:#059669;
                    color:white;
                    border:none;
                    padding:10px 20px;
                    border-radius:6px;
                    cursor:pointer;
                "
            >
                Cập nhật trạng thái
            </button>

        </form>

    </div>

</div>
@endsection