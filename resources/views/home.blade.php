@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Banner Chào mừng -->
    <div class="bg-emerald-800 text-white rounded-2xl p-8 mb-8 text-center shadow-md">
        <h1 class="text-3xl font-bold mb-2">Chào mừng đến với Sàn Nông Sản Việt!</h1>
        <p class="text-emerald-100 mb-6">Nơi kết nối nông sản tươi sạch và đặc sản vùng miền trên toàn quốc.</p>
        
        <!-- Thanh Tìm Kiếm Trực Tiếp -->
        <form action="{{ route('products.index') }}" method="GET" class="max-w-xl mx-auto flex gap-2">
            <input type="text" name="search" placeholder="Nhập tên nông sản cần tìm..." 
                   class="flex-1 px-4 py-3 rounded-xl text-gray-800 border-0 focus:ring-2 focus:ring-emerald-400 outline-none shadow">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl transition shadow">
                Tìm kiếm
            </button>
        </form>
    </div>

    <!-- Khối 1: Danh mục nông sản nổi bật -->
    <div class="mb-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-emerald-600 pl-3">Danh Mục Nông Sản</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('products.index', ['category_id' => 1]) }}" class="bg-emerald-50 hover:bg-emerald-100 p-4 rounded-xl text-center border border-emerald-200 transition">
                <span class="text-3xl">🍎</span>
                <p class="font-semibold text-emerald-800 mt-2">Trái Cây Tươi</p>
            </a>
            <a href="{{ route('products.index', ['category_id' => 2]) }}" class="bg-emerald-50 hover:bg-emerald-100 p-4 rounded-xl text-center border border-emerald-200 transition">
                <span class="text-3xl">🥦</span>
                <p class="font-semibold text-emerald-800 mt-2">Rau Củ Hữu Cơ</p>
            </a>
            <a href="{{ route('products.index', ['category_id' => 3]) }}" class="bg-emerald-50 hover:bg-emerald-100 p-4 rounded-xl text-center border border-emerald-200 transition">
                <span class="text-3xl">☕</span>
                <p class="font-semibold text-emerald-800 mt-2">Đặc Sản Vùng Miền</p>
            </a>
            <a href="{{ route('products.index', ['category_id' => 4]) }}" class="bg-emerald-50 hover:bg-emerald-100 p-4 rounded-xl text-center border border-emerald-200 transition">
                <span class="text-3xl">🌾</span>
                <p class="font-semibold text-emerald-800 mt-2">Nông Sản Khô</p>
            </a>
        </div>
    </div>

    <!-- Khối 2: Danh sách sản phẩm gợi ý -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-emerald-600 pl-3">Sản Phẩm Nổi Bật</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            
            <!-- Thẻ sản phẩm 1 -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center text-5xl">🥭</div>
                <div class="p-4">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-medium px-2 py-0.5 rounded">Đặc sản Miền Tây</span>
                    <h3 class="font-bold text-gray-800 text-lg mt-1">Xoài Cát Hòa Lộc</h3>
                    <p class="text-emerald-600 font-bold mt-2">85.000đ / kg</p>
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">Thêm vào giỏ</button>
                        <form action="{{ route('wishlist.toggle', 1) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-500 text-gray-500 transition" title="Thêm vào yêu thích">❤️</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Thẻ sản phẩm 2 -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center text-5xl">🍓</div>
                <div class="p-4">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-medium px-2 py-0.5 rounded">Đà Lạt</span>
                    <h3 class="font-bold text-gray-800 text-lg mt-1">Dâu Tây Giống Nhật</h3>
                    <p class="text-emerald-600 font-bold mt-2">150.000đ / hộp</p>
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">Thêm vào giỏ</button>
                        <form action="{{ route('wishlist.toggle', 2) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-500 text-gray-500 transition" title="Thêm vào yêu thích">❤️</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Thẻ sản phẩm 3 -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center text-5xl">☕</div>
                <div class="p-4">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-medium px-2 py-0.5 rounded">Tây Nguyên</span>
                    <h3 class="font-bold text-gray-800 text-lg mt-1">Cà Phê Moka Cầu Đất</h3>
                    <p class="text-emerald-600 font-bold mt-2">220.000đ / kg</p>
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">Thêm vào giỏ</button>
                        <form action="{{ route('wishlist.toggle', 3) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-500 text-gray-500 transition" title="Thêm vào yêu thích">❤️</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Thẻ sản phẩm 4 -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center text-5xl">🥑</div>
                <div class="p-4">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-medium px-2 py-0.5 rounded">Đắk Lắk</span>
                    <h3 class="font-bold text-gray-800 text-lg mt-1">Bơ Sáp 034</h3>
                    <p class="text-emerald-600 font-bold mt-2">60.000đ / kg</p>
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2 rounded-lg transition">Thêm vào giỏ</button>
                        <form action="{{ route('wishlist.toggle', 4) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-500 text-gray-500 transition" title="Thêm vào yêu thích">❤️</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection