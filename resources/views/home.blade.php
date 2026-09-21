<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <!-- HEADER / NAVBAR -->
    <header class="bg-emerald-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-bold flex items-center gap-2">
                🌱 Nông Sản Việt
            </a>

            <!-- THANH TÌM KIẾM -->
            <form action="{{ route('home') }}" method="GET" class="flex-1 max-w-md mx-6">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Tìm kiếm sản phẩm (Xoài, Bơ, Cam...)..." 
                       class="w-full px-4 py-2 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </form>

            <div class="flex items-center gap-3">
                <!-- NÚT YÊU THÍCH -->
                <a href="{{ route('wishlist.index') }}" class="bg-emerald-700 hover:bg-emerald-600 px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-medium transition">
                    ❤️ Yêu thích <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">{{ $wishlistCount ?? 0 }}</span>
                </a>

                <!-- NÚT GIỎ HÀNG -->
                <a href="{{ route('cart.index') }}" class="bg-emerald-700 hover:bg-emerald-600 px-3 py-1.5 rounded-full flex items-center gap-1.5 text-sm font-medium transition">
                    🛒 Giỏ hàng <span class="bg-emerald-500 text-white text-xs px-2 py-0.5 rounded-full font-bold">{{ count(session('cart', [])) }}</span>
                </a>

                <!-- AUTH (ĐĂNG NHẬP / ĐĂNG XUẤT) -->
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
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-emerald-600 hover:bg-emerald-500 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                        Đăng nhập
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT (DANH SÁCH SẢN PHẨM) -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <!-- ẢNH SẢN PHẨM -->
                        <div class="h-48 bg-emerald-50 flex items-center justify-center p-4">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full object-contain">
                            @else
                                <span class="text-6xl">🥑</span>
                            @endif
                        </div>

                        <!-- THÔNG TIN SẢN PHẨM -->
                        <div class="p-4">
                            <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-md font-medium">Nông Sản</span>
                            <h3 class="font-bold text-gray-800 text-lg mt-2">{{ $product->name }}</h3>
                            <p class="text-emerald-600 font-bold text-lg mt-1">{{ number_format($product->price, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    <!-- THAO TÁC (FORM MUA HÀNG & THẢ TIM) -->
                    <div class="p-4 pt-0 flex items-center gap-2">
                        <!-- FORM THÊM VÀO GIỎ -->
                        <form action="{{ route('cart.add', $product->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-3 rounded-lg text-sm transition">
                                Thêm vào giỏ
                            </button>
                        </form>

                        <!-- FORM YÊU THÍCH -->
                        <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-500 p-2 rounded-lg transition" title="Yêu thích">
                                ❤️
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Không tìm thấy sản phẩm nào!
                </div>
            @endforelse
        </div>
    </main>

</body>
</html>
