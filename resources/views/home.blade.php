@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h1 class="text-lg font-bold text-gray-900">Bộ lọc</h1>
                    <a
                        href="{{ route('home') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-900"
                    >
                        Xóa tất cả
                    </a>
                </div>

                <form action="{{ route('home') }}" method="GET">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="rating" value="{{ request('rating') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">

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

                    <section class="py-5">
                        <h2 class="mb-3 text-sm font-bold text-gray-800">Khoảng giá</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="home-min-price" class="mb-1 block text-xs text-gray-500">Tối thiểu</label>
                                <input
                                    id="home-min-price"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    name="min_price"
                                    value="{{ request('min_price') }}"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-gray-200 px-2.5 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                                >
                            </div>
                            <div>
                                <label for="home-max-price" class="mb-1 block text-xs text-gray-500">Tối đa</label>
                                <input
                                    id="home-max-price"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    name="max_price"
                                    value="{{ request('max_price') }}"
                                    placeholder="Tối đa"
                                    class="w-full rounded-lg border border-gray-200 px-2.5 py-2 text-sm focus:border-emerald-500 focus:outline-none"
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

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        Áp dụng bộ lọc
                    </button>
                </form>
            </aside>

            <section class="min-w-0">
                <form
                    action="{{ route('home') }}"
                    method="GET"
                    class="mb-5 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row"
                >
                    @foreach ($selectedCategoryIds as $categoryId)
                        <input type="hidden" name="categories[]" value="{{ $categoryId }}">
                    @endforeach
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                    <input type="hidden" name="rating" value="{{ request('rating') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <label class="sr-only" for="home-product-search">Tìm kiếm sản phẩm</label>
                    <input
                        id="home-product-search"
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Tìm kiếm sản phẩm (Xoài, Bơ, Cam...)..."
                        class="min-w-0 flex-1 rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                    >
                    <button
                        type="submit"
                        class="shrink-0 rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        Tìm kiếm
                    </button>
                </form>

                @if (request('search'))
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-gray-600">
                            Kết quả tìm kiếm cho:
                            <strong class="text-emerald-700">“{{ request('search') }}”</strong>
                        </p>
                        <a
                            href="{{ route('home', [
                                'categories' => request('categories', []),
                                'category_id' => request('category_id'),
                                'min_price' => request('min_price'),
                                'max_price' => request('max_price'),
                                'rating' => request('rating'),
                                'sort' => request('sort'),
                            ]) }}"
                            class="text-sm font-semibold text-emerald-600 hover:underline"
                        >
                            Xóa tìm kiếm
                        </a>
                    </div>
                @endif

                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        {{ number_format($products->total()) }} sản phẩm
                    </p>
                    <form
                        action="{{ route('home') }}"
                        method="GET"
                        class="flex flex-wrap items-center gap-2 text-sm text-gray-600 sm:justify-end"
                        id="home-product-sort-form"
                    >
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @foreach (request('categories', []) as $categoryId)
                            <input type="hidden" name="categories[]" value="{{ $categoryId }}">
                        @endforeach
                        @if (request('category_id'))
                            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        @endif
                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                        <input type="hidden" name="rating" value="{{ request('rating') }}">
                        <label for="home-product-sort" class="shrink-0">Sắp xếp</label>
                        <select
                            id="home-product-sort"
                            name="sort"
                            onchange="this.form.submit()"
                            class="max-w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="newest" @selected(! request('sort') || request('sort') === 'newest')>Mới nhất</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                        </select>
                    </form>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($products as $product)
                <article class="h-full bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col">
                    {{-- Ảnh --}}
                    <a
                        href="{{ route('products.show', $product->id) }}"
                        class="h-48 bg-emerald-50 flex items-center justify-center p-4"
                    >
                        @include('products._image', ['product' => $product])
                    </a>

                    {{-- Thông tin --}}
                    <div class="p-4 flex-grow">
                        <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-md font-medium">
                            {{ $product->category->name ?? 'Nông sản' }}
                        </span>

                        <h3 class="min-h-14 line-clamp-2 font-bold text-gray-800 text-lg mt-3">
                            <a
                                href="{{ route('products.show', $product->id) }}"
                                class="hover:text-emerald-600 transition"
                            >
                                {{ $product->name }}
                            </a>
                        </h3>

                        <p class="text-emerald-600 font-bold text-lg mt-1">
                            {{ number_format($product->price, 0, ',', '.') }}đ
                        </p>

                        <p class="text-sm text-gray-500 mt-1">
                            Tồn kho: {{ $product->stock }}
                        </p>
                    </div>

                    {{-- Thao tác --}}
                    <div class="p-4 pt-0 flex items-center gap-2">
                        <form
                            action="{{ route('cart.add', $product) }}"
                            method="POST"
                            class="flex-1"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="quantity"
                                value="1"
                            >

                            <button
                                type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-2 px-3 rounded-lg text-sm transition"
                                @disabled($product->stock <= 0)
                            >
                                @if ($product->stock > 0)
                                    Thêm vào giỏ
                                @else
                                    Hết hàng
                                @endif
                            </button>
                        </form>

                        @auth
                            <form
                                action="{{ route('wishlist.toggle', $product) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="bg-red-50 hover:bg-red-100 text-red-500 p-2 rounded-lg transition"
                                    title="Yêu thích"
                                >
                                    ❤️
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="bg-red-50 hover:bg-red-100 text-red-500 p-2 rounded-lg transition"
                                title="Đăng nhập để sử dụng yêu thích"
                                aria-label="Đăng nhập để sử dụng yêu thích"
                            >
                                ❤️
                            </a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-gray-100 bg-white px-6 py-14 text-center text-gray-500">
                    <div class="text-5xl mb-3">🔍</div>
                    <p class="font-semibold text-gray-800">
                        @if (request('search'))
                            Không tìm thấy sản phẩm nào phù hợp với từ khóa '{{ request('search') }}'.
                        @else
                            Không tìm thấy sản phẩm nào phù hợp với bộ lọc hiện tại.
                        @endif
                    </p>
                    <a
                        href="{{ route('home') }}"
                        class="mt-5 inline-flex rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800"
                    >
                        Xóa bộ lọc / Xem tất cả
                    </a>
                </div>
            @endforelse
                </div>

                <div class="mt-8">
                    {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </section>
        </div>
    </div>
@endsection
