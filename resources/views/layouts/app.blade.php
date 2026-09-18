<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'Nông Sản Việt')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">
    {{-- Header chính --}}
    <header class="bg-[#0e5c36] text-white py-3.5 px-6 shadow-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto flex flex-wrap justify-between items-center gap-4">
            {{-- Logo --}}
            <a
                href="{{ route('home') }}"
                class="text-xl font-bold flex items-center gap-2 hover:opacity-90 transition"
            >
                🌱 <span>Nông Sản Việt</span>
            </a>

            {{-- Menu bên phải --}}
            <div class="flex flex-wrap items-center gap-3 md:gap-5 text-sm">
                {{-- Danh sách sản phẩm --}}
                <a
                    href="{{ route('products.index') }}"
                    class="hover:underline font-medium"
                >
                    Sản phẩm
                </a>

                {{-- Yêu thích --}}
                @auth
                    <a
                        href="{{ route('wishlist.index') }}"
                        class="flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white px-3 py-1.5 rounded-full text-sm font-medium transition"
                    >
                        <span>❤️ Yêu thích</span>

                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ \App\Models\Wishlist::where('user_id', auth()->id())->count() }}
                        </span>
                    </a>
                @endauth

                {{-- Giỏ hàng --}}
                <a
                    href="{{ route('cart.index') }}"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-full text-sm font-medium transition"
                >
                    <span>🛒 Giỏ hàng</span>

                    <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ array_sum(array_column(session('cart', []), 'quantity')) }}
                    </span>
                </a>

                {{-- Thông tin đăng nhập --}}
                @auth
                    <div class="flex items-center gap-3 border-l border-white/20 pl-4">
                        <a
                            href="{{ route('profile') }}"
                            class="font-semibold hover:underline flex items-center gap-1"
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
                                class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                            >
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 border-l border-white/20 pl-4">
                        <a
                            href="{{ route('login') }}"
                            class="hover:underline font-medium"
                        >
                            Đăng nhập
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('register') }}"
                            class="hover:underline font-medium"
                        >
                            Đăng ký
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{-- Thông báo --}}
    @if (session('success'))
        <div class="max-w-6xl w-full mx-auto mt-5 px-4">
            <div class="bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="max-w-6xl w-full mx-auto mt-5 px-4">
            <div class="bg-amber-100 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl">
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-6xl w-full mx-auto mt-5 px-4">
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Nội dung trang --}}
    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs border-t border-gray-700 mt-auto">
        <p>
            © 2026 Nông Sản Việt. Tất cả quyền được bảo lưu.
        </p>
    </footer>

    @stack('scripts')
</body>
</html>