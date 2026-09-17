<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sàn Nông Sản & Đặc Sản Vùng Miền</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <header class="bg-emerald-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <a href="/" class="text-xl font-bold flex items-center gap-2">
            🌱 <span>Nông Sản Việt</span>
        </a>

        <!-- Nút hành động phía bên phải -->
        <div class="space-x-4 flex items-center">
            @auth
                @php
                    $wishlistCount = \App\Models\Wishlist::where('user_id', Auth::id())->count();
                @endphp
                
                <!-- Link Yêu thích -->
                <a href="{{ route('wishlist.index') }}" class="hover:underline flex items-center gap-1 font-medium text-white cursor-pointer">
                    ❤️ Yêu thích ({{ $wishlistCount }})
                </a>

                <!-- Link Xin chào / Profile -->
                <a href="/profile" class="font-medium hover:underline text-white">
                    Xin chào, {{ Auth::user()->name }}
                </a>

                <!-- Nút Đăng xuất -->
                <form action="/logout" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold">
                        Đăng xuất
                    </button>
                </form>
            @else
                <!-- Link Yêu thích khi chưa đăng nhập -->
                <a href="{{ route('login') }}" class="hover:underline flex items-center gap-1 font-medium text-white cursor-pointer">
                    ❤️ Yêu thích (0)
                </a>

                <!-- Nút Đăng nhập -->
                <a href="{{ route('login') }}" class="hover:underline font-medium text-white">Đăng nhập</a>
            @endauth
        </div>
    </div>
</header>

    <!-- MAIN CONTENT -->
    <main class="flex-grow container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-4 text-center">
            <p class="font-semibold">Sàn Thương Mại Điện Tử Nông Sản & Đặc Sản Vùng Miền</p>
            <p class="text-sm text-gray-400 mt-1">© 2026 Nhóm Nông Sản Ecommerce. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>