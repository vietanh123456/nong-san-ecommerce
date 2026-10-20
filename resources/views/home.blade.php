@extends('layouts.app')

@section('content')
<div class="bg-slate-50 pb-14">

    {{-- Banner --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#064e3b] via-[#0e6b43] to-[#16a34a] text-white">
        <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-emerald-300/10"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 md:px-6 lg:grid-cols-2 lg:items-center lg:py-20">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold">
                    🌱 Nông sản Việt chất lượng
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                    Nông sản tươi ngon
                    <span class="block text-amber-300">
                        từ vườn đến bàn ăn
                    </span>
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-emerald-50 sm:text-lg">
                    Khám phá đặc sản vùng miền, rau củ tươi, trái cây,
                    gạo, trà và nhiều sản phẩm từ các nhà bán hàng uy tín.
                </p>

                <form
                    action="{{ route('home') }}"
                    method="GET"
                    class="mt-8 flex max-w-2xl flex-col gap-2 rounded-2xl bg-white p-2 shadow-2xl sm:flex-row"
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3 px-3">
                        <span>🔎</span>

                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Tìm trái cây, rau củ, đặc sản..."
                            class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm text-gray-800 outline-none focus:ring-0"
                        >
                    </div>

                    <button
                        type="submit"
                        class="rounded-xl bg-amber-500 px-6 py-3 font-bold text-white transition hover:bg-amber-600"
                    >
                        Tìm kiếm
                    </button>
                </form>

                <div class="mt-6 flex flex-wrap gap-5 text-sm text-emerald-50">
                    <span>✓ Nguồn gốc rõ ràng</span>
                    <span>✓ Nhà bán hàng uy tín</span>
                    <span>✓ Giao hàng tiện lợi</span>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="rounded-[32px] border border-white/20 bg-white/10 p-6 shadow-2xl">
                    <div class="grid grid-cols-2 gap-4 rounded-3xl bg-white p-6 text-gray-800">
                        <div class="rounded-2xl bg-emerald-50 p-5">
                            <div class="text-4xl">🥬</div>
                            <p class="mt-3 font-bold text-emerald-900">
                                Rau củ tươi
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Thu hoạch mỗi ngày
                            </p>
                        </div>

                        <div class="rounded-2xl bg-orange-50 p-5">
                            <div class="text-4xl">🍊</div>
                            <p class="mt-3 font-bold text-orange-900">
                                Trái cây Việt
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Đặc sản vùng miền
                            </p>
                        </div>

                        <div class="rounded-2xl bg-amber-50 p-5">
                            <div class="text-4xl">🌾</div>
                            <p class="mt-3 font-bold text-amber-900">
                                Gạo và ngũ cốc
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Chọn lọc chất lượng
                            </p>
                        </div>

                        <div class="rounded-2xl bg-lime-50 p-5">
                            <div class="text-4xl">🍯</div>
                            <p class="mt-3 font-bold text-lime-900">
                                Đặc sản Việt
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Hương vị quê nhà
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Thống kê --}}
    <section class="relative z-10 mx-auto -mt-7 max-w-6xl px-4 md:px-6">
        <div class="grid overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl sm:grid-cols-3">
            <div class="flex items-center justify-center gap-4 border-b border-gray-100 px-6 py-5 sm:border-b-0 sm:border-r">
                <span class="text-3xl">🛍️</span>
                <div>
                    <p class="text-2xl font-extrabold text-emerald-800">
                        {{ number_format($marketStats['products'] ?? 0) }}+
                    </p>
                    <p class="text-sm text-gray-500">
                        Sản phẩm chất lượng
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 border-b border-gray-100 px-6 py-5 sm:border-b-0 sm:border-r">
                <span class="text-3xl">🏪</span>
                <div>
                    <p class="text-2xl font-extrabold text-emerald-800">
                        {{ number_format($marketStats['shops'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Nhà bán hàng
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 px-6 py-5">
                <span class="text-3xl">🗂️</span>
                <div>
                    <p class="text-2xl font-extrabold text-emerald-800">
                        {{ number_format($marketStats['categories'] ?? 0) }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Danh mục đa dạng
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Danh mục nổi bật --}}
    @if (($featuredCategories ?? collect())->isNotEmpty())
        @php
            $categoryIcons = [
                'trai-cay' => '🍊',
                'rau-cu' => '🥬',
                'gao-va-ngu-coc' => '🌾',
                'ca-phe-va-tra' => '☕',
                'mat-ong' => '🍯',
                'nam' => '🍄',
                'hat-va-qua-kho' => '🥜',
                'gia-vi-va-dac-san' => '🌶️',
                'thuc-pham-kho' => '🧺',
                'banh-va-dac-san' => '🥮',
                'thuy-hai-san' => '🐟',
                'do-uong' => '🧃',
            ];
        @endphp

        <section class="mx-auto max-w-7xl px-4 pt-14 md:px-6">
            <div class="mb-6 flex items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">
                        Khám phá
                    </p>
                    <h2 class="mt-1 text-2xl font-extrabold text-gray-900 sm:text-3xl">
                        Danh mục nổi bật
                    </h2>
                </div>

                <a
                    href="{{ route('products.index') }}"
                    class="text-sm font-bold text-emerald-700 hover:underline"
                >
                    Xem tất cả →
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8">
                @foreach ($featuredCategories as $category)
                    <a
                        href="{{ route('home', ['category_id' => $category->id]) }}"
                        class="group rounded-2xl border border-gray-100 bg-white p-4 text-center shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg"
                    >
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-3xl">
                            {{ $categoryIcons[$category->slug] ?? '🌿' }}
                        </span>

                        <h3 class="mt-3 line-clamp-2 text-sm font-bold text-gray-800 group-hover:text-emerald-700">
                            {{ $category->name }}
                        </h3>

                        <p class="mt-1 text-xs text-gray-400">
                            {{ number_format($category->products_count) }}
                            sản phẩm
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Sản phẩm --}}
    <section class="mx-auto max-w-7xl px-4 pt-14 md:px-6">
        <div class="mb-7">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">
                Chợ nông sản
            </p>

            <h2 class="mt-1 text-2xl font-extrabold text-gray-900 sm:text-3xl">
                Sản phẩm dành cho bạn
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Lựa chọn sản phẩm từ các nhà bán hàng trên toàn quốc.
            </p>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[270px_minmax(0,1fr)]">

            {{-- Bộ lọc --}}
            <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-gray-900">
                        ⚙️ Bộ lọc
                    </h2>

                    <a
                        href="{{ route('home') }}"
                        class="text-xs font-bold text-emerald-700 hover:underline"
                    >
                        Xóa tất cả
                    </a>
                </div>

                <form action="{{ route('home') }}" method="GET">
                    <input
                        type="hidden"
                        name="search"
                        value="{{ request('search') }}"
                    >

                    <input
                        type="hidden"
                        name="rating"
                        value="{{ request('rating') }}"
                    >

                    <input
                        type="hidden"
                        name="sort"
                        value="{{ request('sort') }}"
                    >

                    <div class="border-b border-gray-100 pb-5">
                        <h3 class="mb-3 text-sm font-bold text-gray-800">
                            Danh mục
                        </h3>

                        @if ($categoryTree->isNotEmpty())
                            <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                                @include('products._category-filter', [
                                    'categories' => $categoryTree,
                                    'selectedCategoryIds' => $selectedCategoryIds,
                                ])
                            </div>
                        @else
                            <p class="text-sm text-gray-500">
                                Chưa có danh mục.
                            </p>
                        @endif
                    </div>

                    <div class="py-5">
                        <h3 class="mb-3 text-sm font-bold text-gray-800">
                            Khoảng giá
                        </h3>

                        <div class="space-y-3">
                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                name="min_price"
                                value="{{ request('min_price') }}"
                                placeholder="Giá từ"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            >

                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                name="max_price"
                                value="{{ request('max_price') }}"
                                placeholder="Giá đến"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            >
                        </div>

                        @error('min_price')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        @error('max_price')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                    >
                        Áp dụng bộ lọc
                    </button>
                </form>
            </aside>

            <div class="min-w-0">
                {{-- Thanh tìm kiếm --}}
                <form
                    action="{{ route('home') }}"
                    method="GET"
                    class="mb-5 flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:flex-row"
                >
                    @foreach ($selectedCategoryIds as $categoryId)
                        <input
                            type="hidden"
                            name="categories[]"
                            value="{{ $categoryId }}"
                        >
                    @endforeach

                    <input
                        type="hidden"
                        name="min_price"
                        value="{{ request('min_price') }}"
                    >

                    <input
                        type="hidden"
                        name="max_price"
                        value="{{ request('max_price') }}"
                    >

                    <input
                        type="hidden"
                        name="rating"
                        value="{{ request('rating') }}"
                    >

                    <input
                        type="hidden"
                        name="sort"
                        value="{{ request('sort') }}"
                    >

                    <div class="flex min-w-0 flex-1 items-center gap-3 rounded-xl bg-gray-50 px-4">
                        <span>🔎</span>

                        <input
                            id="home-product-search"
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Tìm kiếm trong chợ nông sản..."
                            class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm outline-none focus:ring-0"
                        >
                    </div>

                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-800"
                    >
                        Tìm kiếm
                    </button>
                </form>

                @if (request('search'))
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3">
                        <p class="text-sm text-gray-600">
                            Kết quả tìm kiếm cho:
                            <strong class="text-emerald-700">
                                “{{ request('search') }}”
                            </strong>
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
                            class="text-sm font-bold text-emerald-700 hover:underline"
                        >
                            Xóa tìm kiếm
                        </a>
                    </div>
                @endif

                {{-- Sắp xếp --}}
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Tìm thấy
                        <strong class="text-gray-900">
                            {{ number_format($products->total()) }}
                        </strong>
                        sản phẩm
                    </p>

                    <form
                        id="home-product-sort-form"
                        action="{{ route('home') }}"
                        method="GET"
                        class="flex items-center gap-2 text-sm text-gray-600"
                    >
                        <input
                            type="hidden"
                            name="search"
                            value="{{ request('search') }}"
                        >

                        @foreach (request('categories', []) as $categoryId)
                            <input
                                type="hidden"
                                name="categories[]"
                                value="{{ $categoryId }}"
                            >
                        @endforeach

                        @if (request('category_id'))
                            <input
                                type="hidden"
                                name="category_id"
                                value="{{ request('category_id') }}"
                            >
                        @endif

                        <input
                            type="hidden"
                            name="min_price"
                            value="{{ request('min_price') }}"
                        >

                        <input
                            type="hidden"
                            name="max_price"
                            value="{{ request('max_price') }}"
                        >

                        <input
                            type="hidden"
                            name="rating"
                            value="{{ request('rating') }}"
                        >

                        <label for="home-product-sort">
                            Sắp xếp:
                        </label>

                        <select
                            id="home-product-sort"
                            name="sort"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="newest" {{ ! request('sort') || request('sort') === 'newest' ? 'selected' : '' }}>
                                Mới nhất
                            </option>

                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>
                                Giá thấp đến cao
                            </option>

                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>
                                Giá cao đến thấp
                            </option>
                        </select>
                    </form>
                </div>

                {{-- Danh sách sản phẩm --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($products as $product)
                        <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <a
                                href="{{ route('products.show', $product->id) }}"
                                class="relative flex h-52 items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-50 to-lime-50 p-4"
                            >
                                @include('products._image', [
                                    'product' => $product,
                                ])

                                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-emerald-700 shadow-sm">
                                    {{ $product->category->name ?? 'Nông sản' }}
                                </span>

                                <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-xs font-bold text-white shadow-sm {{ $product->stock > 0 ? 'bg-emerald-600' : 'bg-gray-600' }}">
                                    {{ $product->stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                </span>
                            </a>

                            <div class="flex flex-1 flex-col p-5">
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                                    <span>🏪</span>
                                    <span class="truncate">
                                        {{ $product->seller->name ?? 'Nông Sản Việt' }}
                                    </span>
                                </p>

                                <h3 class="mt-3 min-h-12 line-clamp-2 text-base font-extrabold leading-6 text-gray-800">
                                    <a
                                        href="{{ route('products.show', $product->id) }}"
                                        class="transition group-hover:text-emerald-700"
                                    >
                                        {{ $product->name }}
                                    </a>
                                </h3>

                                @if ($product->origin)
                                    <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-500">
                                        <span>📍</span>
                                        <span class="truncate">
                                            {{ $product->origin }}
                                        </span>
                                    </p>
                                @endif

                                <div class="mt-auto pt-4">
                                    <div class="flex items-end justify-between gap-3">
                                        <p class="text-xl font-extrabold text-emerald-700">
                                            {{ number_format($product->price, 0, ',', '.') }}đ
                                        </p>

                                        <p class="text-xs text-gray-400">
                                            Kho: {{ number_format($product->stock) }}
                                        </p>
                                    </div>

                                    <div class="mt-4 flex items-center gap-2">
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
                                                class="w-full rounded-xl bg-emerald-600 px-3 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                                @disabled($product->stock <= 0)
                                            >
                                                {{ $product->stock > 0
                                                    ? '🛒 Thêm vào giỏ'
                                                    : 'Hết hàng'
                                                }}
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
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 hover:bg-red-100"
                                                    title="Yêu thích"
                                                >
                                                    ❤️
                                                </button>
                                            </form>
                                        @else
                                            <a
                                                href="{{ route('login') }}"
                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 hover:bg-red-100"
                                                title="Đăng nhập để sử dụng yêu thích"
                                                aria-label="Đăng nhập để sử dụng yêu thích"
                                            >
                                                ❤️
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-2xl border border-gray-100 bg-white px-6 py-16 text-center shadow-sm">
                            <div class="text-6xl">🔍</div>

                            <p class="mt-4 font-bold text-gray-800">
                                @if (request('search'))
                                    Không tìm thấy sản phẩm nào phù hợp với từ khóa '{{ request('search') }}'.
                                @else
                                    Không tìm thấy sản phẩm nào phù hợp với bộ lọc hiện tại.
                                @endif
                            </p>

                            <a
                                href="{{ route('home') }}"
                                class="mt-5 inline-flex rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-800"
                            >
                                Xóa bộ lọc / Xem tất cả
                            </a>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())
                    <div class="mt-9 overflow-x-auto rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Cam kết --}}
    <section class="mx-auto max-w-7xl px-4 pt-16 md:px-6">
        <div class="grid gap-5 rounded-3xl bg-[#083f2d] p-6 text-white shadow-xl sm:grid-cols-2 lg:grid-cols-4 lg:p-8">
            <div class="flex gap-4 rounded-2xl bg-white/5 p-4">
                <span class="text-3xl">🌿</span>
                <div>
                    <h3 class="font-bold">Nông sản chọn lọc</h3>
                    <p class="mt-1 text-sm text-emerald-100/70">
                        Sản phẩm đa dạng từ nhiều vùng miền.
                    </p>
                </div>
            </div>

            <div class="flex gap-4 rounded-2xl bg-white/5 p-4">
                <span class="text-3xl">🔎</span>
                <div>
                    <h3 class="font-bold">Minh bạch nguồn gốc</h3>
                    <p class="mt-1 text-sm text-emerald-100/70">
                        Dễ dàng kiểm tra thông tin sản phẩm.
                    </p>
                </div>
            </div>

            <div class="flex gap-4 rounded-2xl bg-white/5 p-4">
                <span class="text-3xl">📦</span>
                <div>
                    <h3 class="font-bold">Đóng gói cẩn thận</h3>
                    <p class="mt-1 text-sm text-emerald-100/70">
                        Bảo vệ sản phẩm trong quá trình giao.
                    </p>
                </div>
            </div>

            <div class="flex gap-4 rounded-2xl bg-white/5 p-4">
                <span class="text-3xl">🤝</span>
                <div>
                    <h3 class="font-bold">Hỗ trợ tận tâm</h3>
                    <p class="mt-1 text-sm text-emerald-100/70">
                        Đồng hành cùng người mua và người bán.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
