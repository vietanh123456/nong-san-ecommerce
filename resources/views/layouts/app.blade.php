<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <!-- Header chính -->
    <header class="bg-[#0e5c36] text-white py-3.5 px-6 shadow-md sticky top-0 z-40">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            
            <!-- Logo -->
            <a href="/home" class="text-xl font-bold flex items-center gap-2 hover:opacity-90 transition">
                🌱 <span>Nông Sản Việt</span>
            </a>

            <!-- Menu bên phải: Yêu thích & Tài khoản -->
            <div class="flex items-center gap-5 text-sm">
                <!-- Nút Yêu thích (Đã đồng bộ với DB & Session) -->
                <a href="{{ route('wishlist.index') }}" class="flex items-center gap-2 bg-red-500/20 hover:bg-red-500/30 text-white px-3 py-1.5 rounded-full text-sm font-medium transition">
                    <span>❤️ Yêu thích</span>
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ Auth::check() ? \App\Models\Wishlist::where('user_id', Auth::id())->count() : count(session('wishlist', [])) }}
                    </span>
                </a>

              <a href="{{ route('cart.index') }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-full text-sm font-medium transition">
    <span>🛒 Giỏ hàng</span>
    <span class="bg-white text-emerald-700 text-xs font-bold px-2 py-0.5 rounded-full">
        {{ array_sum(array_column(session('cart', []), 'quantity')) }}
    </span>
</a>

                <!-- Thông tin đăng nhập -->
                @auth
                    <div class="flex items-center gap-3 border-l border-white/20 pl-4">
                        <a href="/profile" class="font-semibold hover:underline flex items-center gap-1">
                            👤 {{ Auth::user()->name }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 border-l border-white/20 pl-4">
                        <a href="/login" class="hover:underline font-medium">Đăng nhập</a>
                        <span>/</span>
                        <a href="/register" class="hover:underline font-medium">Đăng ký</a>
                    </div>
                @endauth
            </div>

        </div>
    </header>

    <!-- Nội dung các trang sẽ nhúng vào đây -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs border-t border-gray-700 mt-auto">
        <p>© 2026 Nông Sản Việt. Tất cả quyền được bảo lưu.</p>
    </footer>

</body>
</html>