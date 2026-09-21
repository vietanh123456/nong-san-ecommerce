@extends('layouts.seller')

@section('title', 'Seller Dashboard')

@push('styles')
    <style>
        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            padding: 22px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .stat-card-highlight {
            border: 2px solid #16a34a;
        }

        .stat-value {
            margin: 8px 0 0;
            color: #166534;
            font-size: 30px;
            font-weight: bold;
        }

        .stat-value-revenue {
            color: #15803d;
        }

        .section-title {
            margin: 10px 0 18px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 22px;
        }

        .section-title p {
            margin-top: 5px;
            color: #6b7280;
            font-size: 14px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .order-actions {
            margin-bottom: 28px;
        }

        @media (max-width: 850px) {
            .statistics-grid,
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush


@section('content')

    {{-- =====================================================
        TIÊU ĐỀ DASHBOARD
    ====================================================== --}}
    <div class="page-heading">

        <div>
            <h1>Seller Dashboard</h1>

            <p>
                Tổng quan sản phẩm, đơn hàng, doanh thu,
                tồn kho và đánh giá.
            </p>
        </div>

        <a
            href="{{ route('seller.products.create') }}"
            class="btn"
        >
            Thêm sản phẩm
        </a>

    </div>


    {{-- =====================================================
        THỐNG KÊ SẢN PHẨM
    ====================================================== --}}

    <div class="section-title">
        <h2>📦 Thống kê sản phẩm</h2>

        <p>
            Thông tin tổng quan về sản phẩm của bạn.
        </p>
    </div>


    <section class="statistics-grid">

        {{-- Tổng sản phẩm --}}
        <article class="stat-card">

            <div>
                Tổng sản phẩm
            </div>

            <div class="stat-value">
                {{ $statistics['total_products'] }}
            </div>

        </article>


        {{-- Sản phẩm đang bán --}}
        <article class="stat-card">

            <div>
                Sản phẩm đang bán
            </div>

            <div class="stat-value">
                {{ $statistics['active_products'] }}
            </div>

        </article>


        {{-- Tổng tồn kho --}}
        <article class="stat-card">

            <div>
                Tổng tồn kho
            </div>

            <div class="stat-value">
                {{ number_format($statistics['total_stock']) }}
            </div>

        </article>


        {{-- Sản phẩm sắp hết --}}
        <article class="stat-card">

            <div>
                Sản phẩm sắp hết
            </div>

            <div class="stat-value">
                {{ $statistics['low_stock_products'] }}
            </div>

        </article>


        {{-- Tổng đánh giá --}}
        <article class="stat-card">

            <div>
                Tổng đánh giá
            </div>

            <div class="stat-value">
                {{ $statistics['total_reviews'] }}
            </div>

        </article>


        {{-- Review chờ duyệt --}}
        <article class="stat-card">

            <div>
                Review chờ duyệt
            </div>

            <div class="stat-value">
                {{ $statistics['pending_reviews'] }}
            </div>

        </article>

    </section>


    {{-- =====================================================
        THỐNG KÊ ĐƠN HÀNG & DOANH THU
    ====================================================== --}}

    <div class="section-title">

        <h2>💰 Đơn hàng & Doanh thu</h2>

        <p>
            Doanh thu được tính từ các sản phẩm của bạn
            trong những đơn hàng đã hoàn thành.
        </p>

    </div>


    <section class="statistics-grid">

        {{-- Tổng đơn hàng --}}
        <article class="stat-card">

            <div>
                Tổng đơn hàng
            </div>

            <div class="stat-value">
                {{ $statistics['total_orders'] ?? 0 }}
            </div>

        </article>


        {{-- Chờ xử lý --}}
        <article class="stat-card">

            <div>
                Chờ xử lý
            </div>

            <div class="stat-value">
                {{ $statistics['pending_orders'] ?? 0 }}
            </div>

        </article>


        {{-- Đang xử lý --}}
        <article class="stat-card">

            <div>
                Đang xử lý
            </div>

            <div class="stat-value">
                {{ $statistics['processing_orders'] ?? 0 }}
            </div>

        </article>


        {{-- Đang giao --}}
        <article class="stat-card">

            <div>
                Đang giao hàng
            </div>

            <div class="stat-value">
                {{ $statistics['shipping_orders'] ?? 0 }}
            </div>

        </article>


        {{-- Hoàn thành --}}
        <article class="stat-card">

            <div>
                Đơn hoàn thành
            </div>

            <div class="stat-value">
                {{ $statistics['completed_orders'] ?? 0 }}
            </div>

        </article>


        {{-- Tổng doanh thu --}}
        <article class="stat-card stat-card-highlight">

            <div>
                💰 Tổng doanh thu
            </div>

            <div class="stat-value stat-value-revenue">

                {{ number_format(
                    $statistics['total_revenue'] ?? 0,
                    0,
                    ',',
                    '.'
                ) }}đ

            </div>

        </article>

    </section>


    {{-- =====================================================
        NÚT QUẢN LÝ ĐƠN HÀNG
    ====================================================== --}}

    <div class="order-actions">

        <a
            href="{{ route('seller.orders.index') }}"
            class="btn"
        >
            📋 Quản lý đơn hàng
        </a>

    </div>


    {{-- =====================================================
        SẢN PHẨM MỚI + TỒN KHO THẤP
    ====================================================== --}}

    <section class="dashboard-grid">

        {{-- Sản phẩm mới --}}
        <div class="card">

            <div class="page-heading">

                <h2>
                    Sản phẩm mới nhất
                </h2>

                <a
                    href="{{ route('seller.products.index') }}"
                    class="btn btn-secondary"
                >
                    Xem tất cả
                </a>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Tồn kho</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($recentProducts as $product)

                            <tr>

                                <td>

                                    <a
                                        href="{{ route(
                                            'seller.products.show',
                                            $product
                                        ) }}"
                                    >
                                        {{ $product->name }}
                                    </a>

                                </td>


                                <td>

                                    {{ $product->category->name ?? 'Chưa có' }}

                                </td>


                                <td>

                                    {{ $product->stock }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3">
                                    Chưa có sản phẩm.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Cảnh báo tồn kho --}}
        <div class="card">

            <h2>
                Cảnh báo tồn kho thấp
            </h2>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Tồn kho</th>
                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($lowStockProducts as $product)

                            <tr>

                                <td>

                                    <a
                                        href="{{ route(
                                            'seller.products.edit',
                                            $product
                                        ) }}"
                                    >
                                        {{ $product->name }}
                                    </a>

                                </td>


                                <td>

                                    {{ $product->category->name ?? 'Chưa có' }}

                                </td>


                                <td>

                                    <span class="badge badge-inactive">

                                        {{ $product->stock }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3">
                                    Không có sản phẩm tồn kho thấp.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </section>

@endsection