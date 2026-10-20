<header class="bg-[#0e5c36] text-white py-3.5 px-6 shadow-md sticky top-0 z-40">
    <div class="max-w-6xl mx-auto flex flex-wrap justify-between items-center gap-4">
        <a
            href="{{ route('home') }}"
            class="text-xl font-bold flex items-center gap-2 hover:opacity-90 transition"
        >
            <span>🌱</span>
            <span>Nông Sản Việt</span>
        </a>

        <div class="flex flex-wrap items-center gap-3 md:gap-5 text-sm">
            <a
                href="{{ route('products.index') }}"
                class="inline-flex items-center px-3 py-2 rounded-lg hover:bg-white/10 font-medium transition"
            >
                Sản phẩm
            </a>

            @auth
                @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 rounded-full text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white"
                    >
                        Admin Panel
                    </a>

                    <a
                        href="{{ route('admin.seller-requests.index') }}"
                        class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-full text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-white"
                    >
                        🌾 Duyệt người bán
                    </a>
                @endif

                @if (auth()->user()->role === 'seller')
                    <a
                        href="{{ route('seller.dashboard') }}"
                        class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                    >
                        ← Seller Dashboard
                    </a>
                @endif

                <a
                    href="{{ route('wishlist.index') }}"
                    class="inline-flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white px-3 py-2 rounded-full font-medium transition"
                >
                    <span>❤️ Yêu thích</span>

                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-full font-medium transition"
                >
                    <span>🛒 Giỏ hàng</span>

                    <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                @if (! in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                    <a
                        href="{{ route('orders.index') }}"
                        class="flex items-center gap-2 bg-amber-500/20 hover:bg-amber-500/30 text-white px-3 py-1.5 rounded-full text-sm font-medium transition"
                    >
                        <span>📦 Đơn hàng</span>
                    </a>
                @endif

                <div class="flex items-center gap-3 border-l border-white/20 pl-4">
                    @if (in_array(auth()->user()->role, ['user', 'buyer'], true))
                        <a
                            href="{{ route('become-seller') }}"
                            class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                        >
                            🌾 Trở thành người bán
                        </a>
                    @endif

                    <a
                        href="{{ route('profile') }}"
                        class="font-semibold hover:underline flex items-center gap-1"
                    >
                        👤 Hồ sơ
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
                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-full font-medium transition"
                >
                    <span>🛒 Giỏ hàng</span>

                    <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

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