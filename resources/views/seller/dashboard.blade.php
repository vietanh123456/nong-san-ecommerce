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

        .stat-value {
            margin: 8px 0 0;
            color: #166534;
            font-size: 30px;
            font-weight: bold;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
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
    <div class="page-heading">
        <div>
            <h1>Seller Dashboard</h1>
            <p>Tổng quan sản phẩm, tồn kho và đánh giá.</p>
        </div>

        <a href="{{ route('seller.products.create') }}" class="btn">
            Thêm sản phẩm
        </a>
    </div>

    <section class="statistics-grid">
        <article class="stat-card">
            <div>Tổng sản phẩm</div>
            <div class="stat-value">
                {{ $statistics['total_products'] }}
            </div>
        </article>

        <article class="stat-card">
            <div>Sản phẩm đang bán</div>
            <div class="stat-value">
                {{ $statistics['active_products'] }}
            </div>
        </article>

        <article class="stat-card">
            <div>Tổng tồn kho</div>
            <div class="stat-value">
                {{ number_format($statistics['total_stock']) }}
            </div>
        </article>

        <article class="stat-card">
            <div>Sản phẩm sắp hết</div>
            <div class="stat-value">
                {{ $statistics['low_stock_products'] }}
            </div>
        </article>

        <article class="stat-card">
            <div>Tổng đánh giá</div>
            <div class="stat-value">
                {{ $statistics['total_reviews'] }}
            </div>
        </article>

        <article class="stat-card">
            <div>Review chờ duyệt</div>
            <div class="stat-value">
                {{ $statistics['pending_reviews'] }}
            </div>
        </article>
    </section>

    <section class="dashboard-grid">
        <div class="card">
            <div class="page-heading">
                <h2>Sản phẩm mới nhất</h2>

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

                                <td>{{ $product->stock }}</td>
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

        <div class="card">
            <h2>Cảnh báo tồn kho thấp</h2>

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