@extends('layouts.seller')

@section('title', 'Sản phẩm của tôi')

@section('content')
    <div class="card">
        <div class="page-heading">
            <div>
                <h1>Sản phẩm của tôi</h1>
                <p>Quản lý sản phẩm, giá bán, đơn vị và tồn kho.</p>
            </div>

            <a href="{{ route('seller.products.create') }}" class="btn">
                Thêm sản phẩm
            </a>
        </div>

        <form method="GET" action="{{ route('seller.products.index') }}" class="mb-5 flex flex-col gap-3 sm:flex-row">
            <label class="sr-only" for="seller-product-search">Tìm sản phẩm của tôi</label>
            <input
                id="seller-product-search"
                name="search"
                type="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Tìm theo tên hoặc mô tả sản phẩm"
            >
            <button type="submit" class="btn shrink-0">
                Tìm kiếm
            </button>
            @if (! empty($filters['search']))
                <a href="{{ route('seller.products.index') }}" class="btn btn-secondary shrink-0">
                    Xóa tìm kiếm
                </a>
            @endif
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá từ</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image)
                                    <img
                                        src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="product-image"
                                    >
                                @else
                                    <div class="product-image"></div>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $product->name }}</strong>

                                <div>
                                    {{ $product->variants->count() }}
                                    lựa chọn
                                </div>
                            </td>

                            <td>
                                {{ $product->category->name ?? 'Chưa có' }}
                            </td>

                            <td>
                                {{ number_format($product->price, 0, ',', '.') }}đ
                            </td>

                            <td>
                                {{ $product->stock }}
                            </td>

                            <td>
                                @if ($product->status)
                                    <span class="badge badge-active">
                                        Đang bán
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        Tạm ẩn
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('seller.products.show', $product) }}"
                                        class="btn btn-secondary"
                                    >
                                        Xem
                                    </a>

                                    <a
                                        href="{{ route('seller.products.edit', $product) }}"
                                        class="btn btn-warning"
                                    >
                                        Sửa
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('seller.products.destroy', $product) }}"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                Bạn chưa có sản phẩm nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $products->links() }}
        </div>
    </div>
@endsection