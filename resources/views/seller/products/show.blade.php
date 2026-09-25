@extends('layouts.seller')

@section('title', 'Chi tiết sản phẩm')

@push('styles')
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
        }

        .detail-image {
            width: 100%;
            height: 320px;
            border-radius: 10px;
            object-fit: cover;
            background: #e5e7eb;
        }

        .detail-list {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 12px;
        }

        .detail-label {
            font-weight: bold;
        }

        .section {
            margin-top: 30px;
        }

        .variant-image {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            object-fit: cover;
            background: #e5e7eb;
            border: 1px solid #e5e7eb;
        }

        .variant-image-fallback {
            opacity: 0.65;
        }

        .variant-no-image {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 11px;
            text-align: center;
        }

        .review-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .review-stars {
            color: #d97706;
            font-size: 18px;
            white-space: nowrap;
        }

        .review-date {
            color: #6b7280;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }

            .review-summary {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="page-heading">
            <div>
                <h1>{{ $product->name }}</h1>
                <p>Thông tin chi tiết sản phẩm.</p>
            </div>

            <div class="actions">
                <a
                    href="{{ route('seller.products.edit', $product) }}"
                    class="btn btn-warning"
                >
                    Sửa sản phẩm
                </a>

                <a
                    href="{{ route('seller.products.index') }}"
                    class="btn btn-secondary"
                >
                    Quay lại
                </a>
            </div>
        </div>

        <div class="product-detail">
            <div>
                @if ($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        class="detail-image"
                    >
                @else
                    <div class="detail-image"></div>
                @endif
            </div>

            <div class="detail-list">
                <div class="detail-label">Danh mục</div>
                <div>
                    {{ $product->category->name ?? 'Chưa có' }}
                </div>

                <div class="detail-label">Xuất xứ</div>
                <div>
                    {{ $product->origin ?: 'Chưa cập nhật' }}
                </div>

                <div class="detail-label">Giá thấp nhất</div>
                <div>
                    {{ number_format($product->price, 0, ',', '.') }} đ
                </div>

                <div class="detail-label">Tổng tồn kho</div>
                <div>{{ $product->stock }}</div>

                <div class="detail-label">Trạng thái</div>
                <div>
                    @if ($product->status)
                        <span class="badge badge-active">
                            Đang bán
                        </span>
                    @else
                        <span class="badge badge-inactive">
                            Tạm ẩn
                        </span>
                    @endif
                </div>

                <div class="detail-label">Mô tả</div>
                <div>
                    {!! nl2br(e(
                        $product->description
                        ?: 'Chưa có mô tả'
                    )) !!}
                </div>
            </div>
        </div>

        <section class="section">
            <h2>Phân loại, giá và tồn kho</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên phân loại</th>
                            <th>SKU</th>
                            <th>Đơn vị / Quy cách</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($product->variants as $variant)
                            <tr>
                                <td>
                                    @if ($variant->image)
                                        <img
                                            src="{{ asset('storage/' . $variant->image) }}"
                                            alt="{{ $variant->display_name }}"
                                            class="variant-image"
                                        >
                                    @elseif ($product->image)
                                        <img
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $variant->display_name }}"
                                            class="variant-image variant-image-fallback"
                                            title="Đang dùng ảnh chung của sản phẩm"
                                        >
                                    @else
                                        <div class="variant-no-image">
                                            Không có ảnh
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong>
                                        {{ $variant->display_name }}
                                    </strong>

                                    @if (!$variant->image && $product->image)
                                        <div>
                                            <small>
                                                Dùng ảnh chung
                                            </small>
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $variant->sku }}</td>

                                <td>
                                    @if (
                                        $variant->quantity !== null &&
                                        $variant->unit
                                    )
                                        {{ rtrim(
                                            rtrim(
                                                number_format(
                                                    (float) $variant->quantity,
                                                    2,
                                                    ',',
                                                    ''
                                                ),
                                                '0'
                                            ),
                                            ','
                                        ) }}

                                        {{ $variant->unit->symbol }}
                                    @elseif ($variant->unit)
                                        {{ $variant->unit->name }}
                                        ({{ $variant->unit->symbol }})
                                    @else
                                        <span>Không áp dụng</span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format(
                                        $variant->price,
                                        0,
                                        ',',
                                        '.'
                                    ) }} đ
                                </td>

                                <td>{{ $variant->stock }}</td>

                                <td>
                                    @if ($variant->status)
                                        <span class="badge badge-active">
                                            Đang bán
                                        </span>
                                    @else
                                        <span class="badge badge-inactive">
                                            Tạm ẩn
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    Sản phẩm chưa có phân loại bán.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section">
            <div class="review-summary">
                <div>
                    <h2>Đánh giá và bình luận</h2>

                    <p>
                        Các đánh giá công khai của khách hàng.
                    </p>
                </div>

                <span class="badge badge-active">
                    {{ $product->reviews->count() }} đánh giá
                </span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Số sao</th>
                            <th>Bình luận</th>
                            <th>Ngày gửi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($product->reviews as $review)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $review->user->name ?? 'Người dùng' }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="review-stars">
                                        @for ($star = 1; $star <= 5; $star++)
                                            {{ $star <= $review->rating ? '★' : '☆' }}
                                        @endfor
                                    </div>

                                    <small>
                                        {{ $review->rating }}/5
                                    </small>
                                </td>

                                <td>
                                    {{ $review->comment ?: 'Không có bình luận' }}
                                </td>

                                <td class="review-date">
                                    {{ $review->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    Sản phẩm chưa có đánh giá.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection