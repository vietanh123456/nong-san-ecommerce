<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nông Sản Việt</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-gray-50 min-h-screen">
    {{-- Header --}}
    <header class="bg-emerald-800 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            {{-- Logo --}}
            <a
                href="{{ route('home') }}"
                class="text-2xl font-bold flex items-center gap-2 whitespace-nowrap"
            >
                <span>🌱</span>
                <span>Nông Sản Việt</span>
            </a>

            {{-- Menu --}}
            <nav class="flex flex-wrap items-center gap-2">
                {{-- Seller Dashboard --}}
                @auth
                    @if (Auth::user()->role === 'admin')
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="bg-emerald-700 hover:bg-emerald-600 px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white"
                        >
                            🛡️ {{ Auth::user()->name }}
                        </a>
                    @elseif (Auth::user()->role === 'seller')
                        <a
                            href="{{ route('seller.dashboard') }}"
                            class="bg-emerald-700 hover:bg-emerald-600 px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white"
                        >
                            🏪 Kênh người bán
                        </a>
                    @else
                        <a
                            href="{{ route('profile') }}"
                            class="text-sm font-medium text-emerald-100 hover:text-white hover:underline"
                        >
                            👤 {{ Auth::user()->name }}
                        </a>
                        <a
                            href="{{ route('become-seller') }}"
                            class="bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white"
                        >
                            🌾 Trở thành người bán
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf

                        <button
                            type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-xs font-semibold transition"
                        >
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="bg-emerald-600 hover:bg-emerald-500 px-3 py-2 rounded-lg text-xs font-semibold transition"
                    >
                        Đăng nhập
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="border border-white/40 hover:bg-white/10 px-3 py-2 rounded-lg text-xs font-semibold transition"
                    >
                        Đăng ký
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Nội dung --}}
    <main class="max-w-7xl mx-auto px-4 py-8">
        {{-- Thông báo --}}
        @if (session('success'))
            <div class="mb-5 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-5 p-4 bg-amber-100 border border-amber-400 text-amber-700 rounded-lg">
                {{ session('warning') }}
            </div>
        @endif

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
                                <label for="home-min-price" class="mb-1 block text-xs text-gray-500">Từ (đ)</label>
                                <input
                                    id="home-min-price"
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
                                <label for="home-max-price" class="mb-1 block text-xs text-gray-500">Đến (đ)</label>
                                <input
                                    id="home-max-price"
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
                            href="{{ route('home') }}"
                            class="text-sm font-semibold text-emerald-600 hover:underline"
                        >
                            Xóa tìm kiếm
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($products as $product)
                <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col">
                    {{-- Ảnh --}}
                    <a
                        href="{{ route('products.show', $product->id) }}"
                        class="h-48 bg-emerald-50 flex items-center justify-center p-4"
                    >
                        @if ($product->image)
                            <img
                                src="{{ asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-contain"
                            >
                        @else
                            <span class="text-6xl">🥑</span>
                        @endif
                    </a>

                    {{-- Thông tin --}}
                    <div class="p-4 flex-grow">
                        <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-md font-medium">
                            {{ $product->category->name ?? 'Nông sản' }}
                        </span>

                        <h3 class="font-bold text-gray-800 text-lg mt-3">
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
    </main>
</body>
</html>
