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

            {{-- Tìm kiếm --}}
            <form
                action="{{ route('home') }}"
                method="GET"
                class="w-full lg:flex-1 lg:max-w-md lg:mx-6"
            >
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm kiếm sản phẩm (Xoài, Bơ, Cam...)..."
                    class="w-full px-4 py-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                >
            </form>

            {{-- Menu --}}
            <nav class="flex flex-wrap items-center gap-2">
                {{-- Seller Dashboard --}}
                @auth
                    @if (auth()->user()->role === 'seller')
                        <a
                            href="{{ route('seller.dashboard') }}"
                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg flex items-center gap-1.5 text-sm font-semibold transition"
                        >
                            <span>🏪</span>
                            <span>Seller Dashboard</span>
                        </a>
                    @endif
                @endauth

                {{-- Yêu thích --}}
                <a
                    href="{{ route('wishlist.index') }}"
                    class="bg-emerald-700 hover:bg-emerald-600 px-3 py-2 rounded-full flex items-center gap-1.5 text-sm font-medium transition"
                >
                    <span>❤️ Yêu thích</span>

                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                {{-- Giỏ hàng --}}
                <a
                    href="{{ route('cart.index') }}"
                    class="bg-emerald-700 hover:bg-emerald-600 px-3 py-2 rounded-full flex items-center gap-1.5 text-sm font-medium transition"
                >
                    <span>🛒 Giỏ hàng</span>

                    <span class="bg-emerald-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                        {{ array_sum(array_column(session('cart', []), 'quantity')) }}
                    </span>
                </a>

                {{-- Tài khoản --}}
                @auth
                    <a
                        href="{{ route('profile') }}"
                        class="text-sm font-medium text-emerald-100 hover:text-white hover:underline"
                    >
                        👤 {{ auth()->user()->name }}
                    </a>

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="inline"
                    >
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

        {{-- Kết quả tìm kiếm --}}
        @if (request('search'))
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <p class="text-gray-600">
                    Kết quả tìm kiếm cho:
                    <strong class="text-emerald-700">
                        “{{ request('search') }}”
                    </strong>
                </p>

                <a
                    href="{{ route('home') }}"
                    class="text-sm font-semibold text-emerald-600 hover:underline"
                >
                    Xóa tìm kiếm
                </a>
            </div>
        @endif

        {{-- Danh sách sản phẩm --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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
                            @if (auth()->user()->role === 'customer')
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
                            @endif
                        @endauth
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    <div class="text-5xl mb-3">🔍</div>
                    <p>Không tìm thấy sản phẩm nào!</p>
                </div>
            @endforelse
        </div>
    </main>
</body>
</html>