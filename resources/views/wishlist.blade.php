@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Tiêu đề -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <span>❤️</span> Danh sách sản phẩm yêu thích
        </h1>
        <span class="text-sm text-gray-500">Đã lưu {{ $wishlists->count() }} sản phẩm</span>
    </div>

    <!-- Thông báo -->
    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded-xl mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Hiển thị danh sách sản phẩm -->
    @if($wishlists->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($wishlists as $item)
                @php
                    $product = $item->product;
                    $productId = $product->id ?? $item->product_id;
                    $productName = $product->name ?? 'Sản phẩm nông sản #' . $item->product_id;
                    $productPrice = $product->price ?? 50000;
                    $productImage = $product->image ?? null;
                @endphp

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition group">
                    
                    <!-- Ảnh và nút xóa -->
                    <div class="relative bg-gray-50 h-48 overflow-hidden flex items-center justify-center">
                        @if($productImage)
                            <img src="{{ $productImage }}" alt="{{ $productName }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <span class="text-6xl">🥑</span>
                        @endif

                        <!-- Nút Bỏ yêu thích -->
                        <form action="{{ route('wishlist.toggle', $productId) }}" method="POST" class="absolute top-3 right-3">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-full bg-white/90 text-red-500 hover:bg-red-50 flex items-center justify-center shadow-sm font-bold text-sm transition" title="Xóa khỏi yêu thích">
                                ✕
                            </button>
                        </form>
                    </div>

                    <!-- Thông tin sản phẩm -->
                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base mb-1 line-clamp-1">
                                {{ $productName }}
                            </h3>
                            <p class="text-emerald-600 font-bold text-lg mb-4">
                                {{ number_format($productPrice, 0, ',', '.') }} đ
                            </p>
                        </div>

                        <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs py-2.5 rounded-xl transition">
                            Thêm vào giỏ hàng
                        </button>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <!-- Trạng thái trống -->
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 my-6 shadow-sm">
            <div class="text-6xl mb-4">💔</div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Chưa có sản phẩm yêu thích</h3>
            <p class="text-gray-500 text-xs mb-6">Hãy bấm vào biểu tượng trái tim ở các sản phẩm bạn thích để lưu lại nhé!</p>
            <a href="/home" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-6 py-3 rounded-xl transition">
                Khám phá nông sản ngay
            </a>
        </div>
    @endif
</div>
@endsection