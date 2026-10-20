@extends('layouts.seller')

@section('title', 'Quản lý đơn hàng')

@push('styles')
    <style>
        .order-list {
            display: grid;
            gap: 16px;
        }

        .order-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: white;
            box-shadow: 0 8px 28px rgba(15, 59, 40, 0.055);
        }

        .order-card-header,
        .order-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 20px;
        }

        .order-card-header {
            border-bottom: 1px solid #edf1ef;
            background: #fbfdfc;
        }

        .order-code {
            margin: 0;
            color: #173d2e;
            font-size: 16px;
        }

        .order-date {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .status-pending {
            color: #9a5b05;
            background: #fff4d6;
        }

        .status-processing {
            color: #1d4ed8;
            background: #e8f0ff;
        }

        .status-shipping {
            color: #6d28d9;
            background: #f0e9ff;
        }

        .status-completed {
            color: #087443;
            background: #dff8e9;
        }

        .status-cancelled {
            color: #b42318;
            background: #ffebe9;
        }

        .order-items {
            padding: 5px 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 14px 0;
            border-bottom: 1px dashed #e2e9e5;
        }

        .order-item:last-child {
            border-bottom: 0;
        }

        .order-item strong,
        .order-item span {
            display: block;
        }

        .order-item strong {
            color: #29473b;
        }

        .order-item span {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        .order-price {
            color: #087443;
            font-weight: 800;
            white-space: nowrap;
        }

        .order-card-footer {
            border-top: 1px solid #edf1ef;
            background: #fbfdfc;
        }

        .customer-name {
            color: #3e554c;
            line-height: 1.55;
        }

        .empty-state {
            padding: 55px 25px;
            border: 1px dashed #bdd3c7;
            border-radius: 18px;
            color: var(--muted);
            background: white;
            text-align: center;
        }

        .empty-state-icon {
            display: block;
            margin-bottom: 12px;
            font-size: 38px;
        }

        @media (max-width: 640px) {
            .order-card-header,
            .order-card-footer,
            .order-item {
                align-items: flex-start;
                flex-direction: column;
            }

            .order-card-footer .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div>
            <h1>Quản lý đơn hàng</h1>
            <p>
                Theo dõi và xử lý các đơn hàng có sản phẩm của cửa hàng.
            </p>
        </div>

        <a href="{{ route('seller.products.index') }}" class="btn btn-secondary">
            Xem sản phẩm
        </a>
    </div>

    @if ($orders->isEmpty())
        <div class="empty-state">
            <span class="empty-state-icon">🧾</span>
            <strong>Chưa có đơn hàng</strong>
            <p>Chưa có đơn hàng nào chứa sản phẩm của bạn.</p>
        </div>
    @else
        <section class="order-list">
            @foreach ($orders as $order)
                @php
                    $statusLabels = [
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'shipping' => 'Đang giao hàng',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp

                <article class="order-card">
                    <header class="order-card-header">
                        <div>
                            <h2 class="order-code">
                                Đơn hàng #{{ $order->id }}
                            </h2>
                            <div class="order-date">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <span class="order-status status-{{ $order->status }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </header>

                    <div class="order-items">
                        @foreach ($order->orderDetails as $detail)
                            <div class="order-item">
                                <div>
                                    <strong>
                                        {{ $detail->product->name ?? 'Sản phẩm không tồn tại' }}
                                    </strong>
                                    <span>
                                        Số lượng: {{ $detail->quantity }}
                                    </span>
                                </div>

                                <div class="order-price">
                                    {{ number_format($detail->subtotal, 0, ',', '.') }}đ
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <footer class="order-card-footer">
                        <div class="customer-name">
                            <strong>Khách hàng:</strong>
                            {{ $order->customer_snapshot['customer_name']
                                ?? $order->user?->name
                                ?? 'Khách không có tài khoản'
                            }}
                        </div>

                        <a
                            href="{{ route('seller.orders.show', $order) }}"
                            class="btn"
                        >
                            Xem chi tiết →
                        </a>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
@endsection
