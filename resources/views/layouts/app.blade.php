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
    @php
        $isAdminArea = request()->routeIs('admin.*');
        $cartCount = array_sum(
            array_column(session('cart', []), 'quantity')
        );
    @endphp

    <header class="bg-[#0e5c36] text-white py-3.5 px-4 md:px-6 shadow-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
            <a
                href="{{ route('home') }}"
                class="text-xl font-bold flex items-center gap-2 hover:opacity-90 transition"
            >
                <span>🌱</span>
                <span>Nông Sản Việt</span>
            </a>

            <nav class="flex flex-wrap items-center gap-2 md:gap-3 text-sm">
                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex items-center px-3 py-2 rounded-lg hover:bg-white/10 font-medium transition"
                >
                    Sản phẩm
                </a>

                @auth
                    @if (auth()->user()->role === 'admin')
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold transition"
                        >
                            <span>🛡️</span>
                            <span>Admin Panel</span>
                        </a>
                    @elseif (auth()->user()->role === 'seller')
                        <a
                            href="{{ route('seller.dashboard') }}"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg font-semibold transition"
                        >
                            <span>🏪</span>
                            <span>Seller Dashboard</span>
                        </a>
                    @endif
                @endauth

                @auth
                    @if (auth()->user()->role === 'customer')
                        <a
                            href="{{ route('wishlist.index') }}"
                            class="inline-flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white px-3 py-2 rounded-full font-medium transition"
                        >
                            <span>❤️ Yêu thích</span>

                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ \App\Models\Wishlist::where(
                                    'user_id',
                                    auth()->id()
                                )->count() }}
                            </span>
                        </a>
                    @endif
                @endauth

                @guest
                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-full font-medium transition"
                    >
                        <span>🛒 Giỏ hàng</span>

                        <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $cartCount }}
                        </span>
                    </a>
                @endguest

                @auth
                    @if (auth()->user()->role === 'customer')
                        <a
                            href="{{ route('cart.index') }}"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-full font-medium transition"
                        >
                            <span>🛒 Giỏ hàng</span>

                            <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ $cartCount }}
                            </span>
                        </a>
                    @endif
                @endauth

                @auth
                    @if (auth()->user()->role !== 'admin')
                        <a
                            href="{{ route('orders.index') }}"
                            class="inline-flex items-center gap-2 bg-amber-500/20 hover:bg-amber-500/30 text-white px-3 py-2 rounded-full font-medium transition"
                        >
                            <span>📦 Đơn hàng</span>
                        </a>
                    @endif
                @endauth

                @auth
                    <div class="flex flex-wrap items-center gap-2 border-l border-white/20 pl-3">
                        <a
                            href="{{ route('profile') }}"
                            class="inline-flex items-center gap-1 font-semibold hover:underline"
                        >
                            <span>👤</span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                            class="inline"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition"
                            >
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 border-l border-white/20 pl-3">
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
            </nav>
        </div>
    </header>

    @if (session('success'))
        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">
            <div
                role="status"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-emerald-300/15 border border-emerald-300/30 text-emerald-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">
            <div
                role="status"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-amber-300/15 border border-amber-300/30 text-amber-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-amber-100 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">
            <div
                role="alert"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-rose-300/15 border border-rose-300/30 text-rose-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs border-t border-gray-700 mt-auto">
        <p>
            © 2026 Nông Sản Việt. Tất cả quyền được bảo lưu.
        </p>
    </footer>

    @stack('scripts')
</body>
</html>