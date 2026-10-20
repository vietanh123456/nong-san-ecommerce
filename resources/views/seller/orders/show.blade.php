@extends('layouts.seller')

@section('title', 'Chi tiết đơn hàng #'.$order->id)

@push('styles')
    <style>
        .order-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.75fr);
            gap: 20px;
            align-items: start;
        }

        .detail-stack {
            display: grid;
            gap: 20px;
        }

        .detail-card {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: white;
            box-shadow: 0 8px 28px rgba(15, 59, 40, 0.055);
        }

        .detail-card h2 {
            margin: 0 0 18px;
            color: #173d2e;
            font-size: 18px;
            letter-spacing: -0.025em;
        }

        .info-list {
            display: grid;
            gap: 0;
            margin: 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 18px;
            padding: 12px 0;
            border-bottom: 1px solid #edf1ef;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-row dt {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .info-row dd {
            margin: 0;
            color: #29473b;
            font-weight: 700;
        }

        .payment-paid,
        .payment-unpaid,
        .current-status {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .payment-paid {
            color: #087443;
            background: #dff8e9;
        }

        .payment-unpaid {
            color: #b42318;
            background: #ffebe9;
        }

        .current-status {
            color: #1d4ed8;
            background: #e8f0ff;
        }

        .product-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px dashed #dde6e1;
        }

        .product-row:last-child {
            border-bottom: 0;
        }

        .product-name {
            color: #29473b;
            font-weight: 800;
        }

        .product-meta {
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.65;
        }

        .product-total {
            color: var(--primary);
            font-weight: 800;
            white-space: nowrap;
        }

        .status-form {
            position: sticky;
            top: 92px;
        }

        .status-note {
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 12px;
            color: #365248;
            background: #f1f7f4;
            line-height: 1.55;
        }

        .status-form .btn {
            width: 100%;
            margin-top: 14px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 13px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            .order-detail-grid {
                grid-template-columns: 1fr;
            }

            .status-form {
                position: static;
            }
        }

        @media (max-width: 560px) {
            .info-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .product-row {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipping' => 'Đang giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    @endphp

    <a href="{{ route('seller.orders.index') }}" class="back-link">
        ← Quay lại danh sách đơn hàng
    </a>

    <div class="page-heading">
        <div>
            <h1>Chi tiết đơn hàng #{{ $order->id }}</h1>
            <p>
                Kiểm tra sản phẩm, thông tin giao nhận và cập nhật tiến độ.
            </p>
        </div>

        <span class="current-status">
            {{ $statusLabels[$order->status] ?? $order->status }}
        </span>
    </div>

    <div class="order-detail-grid">
        <div class="detail-stack">
            <section class="detail-card">
                <h2>Thông tin đơn hàng</h2>

                <dl class="info-list">
                    <div class="info-row">
                        <dt>Mã đơn</dt>
                        <dd>#{{ $order->id }}</dd>
                    </div>

                    <div class="info-row">
                        <dt>Ngày đặt</dt>
                        <dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div class="info-row">
                        <dt>Khách hàng</dt>
                        <dd>
                            {{ $order->customer_snapshot['customer_name']
                                ?? $order->user?->name
                                ?? 'Khách không có tài khoản'
                            }}
                        </dd>
                    </div>

                    <div class="info-row">
                        <dt>Phương thức thanh toán</dt>
                        <dd>
                            {{ $order->payment_method === 'cod'
                                ? 'Thanh toán khi nhận hàng (COD)'
                                : strtoupper($order->payment_method)
                            }}
                        </dd>
                    </div>

                    <div class="info-row">
                        <dt>Thanh toán</dt>
                        <dd>
                            @if ($order->payment_status === 'paid')
                                <span class="payment-paid">Đã thanh toán</span>
                            @else
                                <span class="payment-unpaid">Chưa thanh toán</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="detail-card">
                <h2>Thông tin nhận hàng</h2>
                @include('orders.partials.customer-info')
            </section>

            <section class="detail-card">
                <h2>Sản phẩm của cửa hàng</h2>

                @foreach ($order->orderDetails as $detail)
                    <div class="product-row">
                        <div>
                            <div class="product-name">
                                {{ $detail->product->name ?? 'Sản phẩm không tồn tại' }}
                            </div>
                            <div class="product-meta">
                                Số lượng: {{ $detail->quantity }}<br>
                                Đơn giá:
                                {{ number_format($detail->price, 0, ',', '.') }}đ
                            </div>
                        </div>

                        <div class="product-total">
                            {{ number_format($detail->subtotal, 0, ',', '.') }}đ
                        </div>
                    </div>
                @endforeach
            </section>
        </div>

        <aside class="detail-card status-form">
            <h2>Xử lý đơn hàng</h2>

            <div class="status-note">
                Trạng thái hiện tại:
                <strong>
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </strong>
            </div>

            <form
                action="{{ route('seller.orders.status', $order) }}"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <label for="order_status">Chọn trạng thái mới</label>

                <select id="order_status" name="status" required>
                    <option value="pending" @selected($order->status === 'pending')>
                        Chờ xử lý
                    </option>
                    <option value="processing" @selected($order->status === 'processing')>
                        Đang xử lý
                    </option>
                    <option value="shipping" @selected($order->status === 'shipping')>
                        Đang giao hàng
                    </option>
                    <option value="completed" @selected($order->status === 'completed')>
                        Hoàn thành
                    </option>
                    <option value="cancelled" @selected($order->status === 'cancelled')>
                        Hủy đơn
                    </option>
                </select>

                <button type="submit" class="btn">
                    Cập nhật trạng thái
                </button>
            </form>
        </aside>
    </div>
@endsection
