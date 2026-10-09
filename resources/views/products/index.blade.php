@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <form action="{{ route('products.index') }}" method="GET">
        <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row">
            <label class="sr-only" for="product-search">Tìm kiếm sản phẩm</label>
            <input
                id="product-search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Tìm sản phẩm..."
                class="min-w-0 flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
            >
            <button
                type="submit"
                class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
            >
                Tìm kiếm
            </button>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h1 class="text-lg font-bold text-gray-900">Bộ lọc</h1>
                    <a
                        href="{{ route('products.index') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-900"
                    >
                        Xóa tất cả
                    </a>
                </div>

                <section class="border-b border-gray-100 pb-5">
                    <h2 class="mb-3 text-sm font-bold text-gray-800">Danh mục</h2>
                    @if ($categoryTree->isNotEmpty())
                        <div class="space-y-1">
                            @include('products._category-filter', [
                                'categories' => $categoryTree,
                                'selectedCategoryIds' => $selectedCategoryIds,
                            ])
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Chưa có danh mục.</p>
                    @endif
                </section>

                <section class="border-b border-gray-100 py-5">
                    <h2 class="mb-3 text-sm font-bold text-gray-800">Khoảng giá</h2>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="min-price" class="mb-1 block text-xs text-gray-500">Từ (đ)</label>
                            <input
                                id="min-price"
                                type="number"
                                name="min_price"
                                min="0"
                                step="1000"
                                value="{{ request('min_price') }}"
                                placeholder="0"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            >
                        </div>
                        <div>
                            <label for="max-price" class="mb-1 block text-xs text-gray-500">Đến (đ)</label>
                            <input
                                id="max-price"
                                type="number"
                                name="max_price"
                                min="0"
                                step="1000"
                                value="{{ request('max_price') }}"
                                placeholder="Không giới hạn"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                            >
                        </div>
                    </div>
                    @error('min_price')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('max_price')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <section class="py-5">
                    <h2 class="mb-3 text-sm font-bold text-gray-800">Đánh giá</h2>
                    <label for="rating" class="mb-1 block text-xs text-gray-500">
                        Điểm trung bình tối thiểu
                    </label>
                    <select
                        id="rating"
                        name="rating"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    >
                        <option value="">Tất cả đánh giá</option>
                        @foreach ([5, 4, 3, 2, 1] as $rating)
                            <option value="{{ $rating }}" @selected(request('rating') == $rating)>
                                {{ str_repeat('★', $rating) }} trở lên
                            </option>
                        @endforeach
                    </select>
                    @error('rating')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </section>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    Áp dụng bộ lọc
                </button>
            </aside>

            <main class="min-w-0">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Sản phẩm nông sản</h2>
                        @if (request('search'))
                            <p class="mt-1 text-sm text-gray-600">
                                Kết quả cho “{{ request('search') }}”
                            </p>
                        @endif
                        <p class="mt-1 text-sm text-gray-500">
                            {{ number_format($products->total()) }} sản phẩm
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        Sắp xếp
                        <select
                            name="sort"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Mới nhất</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                        </select>
                    </label>
                </div>

                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <article class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                                <a
                                    href="{{ route('products.show', $product->id) }}"
                                    class="flex h-48 items-center justify-center overflow-hidden bg-gray-50"
                                >
                                    @if ($product->image)
                                        <img
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <span class="text-5xl" aria-hidden="true">🥑</span>
                                    @endif
                                </a>

                                <div class="flex flex-1 flex-col justify-between p-4">
                                    <div>
                                        <p class="mb-1 text-xs text-gray-500">{{ $product->category->name ?? '' }}</p>
                                        <a
                                            href="{{ route('products.show', $product->id) }}"
                                            class="line-clamp-1 font-bold text-gray-800 hover:text-emerald-700"
                                        >
                                            {{ $product->name }}
                                        </a>
                                        <p class="mb-4 mt-2 text-lg font-bold text-emerald-700">
                                            {{ number_format($product->price ?? 0, 0, ',', '.') }} đ
                                        </p>
                                    </div>
                                    <a
                                        href="{{ route('products.show', $product->id) }}"
                                        class="rounded-xl bg-emerald-700 py-2.5 text-center text-xs font-semibold text-white transition hover:bg-emerald-800"
                                    >
                                        Xem chi tiết
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="rounded-2xl border border-gray-100 bg-white px-6 py-14 text-center">
                        <div class="mb-4 text-5xl" aria-hidden="true">🔍</div>
                        <h3 class="font-bold text-gray-800">Không tìm thấy sản phẩm nào</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Hãy thử thay đổi tiêu chí lọc hoặc xóa bộ lọc.
                        </p>
                    </div>
                @endif
            </main>
        </div>
    </form>
</div>
@endsection
