@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    <!-- Nút quay lại -->
    <a href="{{ route('products.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-emerald-600 mb-6 transition">
        ← Quay lại danh sách sản phẩm
    </a>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Khung ảnh sản phẩm -->
        <div class="bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center min-h-[320px] border border-gray-100">
            @if($product->image)
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-8xl">🥑</span>
            @endif
        </div>

        <!-- Thông tin sản phẩm -->
        <div class="flex flex-col justify-between">
            <div>
                <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full uppercase">
                    {{ $product->category->name ?? 'Nông Sản' }}
                </span>
                
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mt-3 mb-2">
                    {{ $product->name }}
                </h1>
                
                <p class="text-3xl font-bold text-emerald-600 mb-4">
                    {{ number_format($product->price ?? 0, 0, ',', '.') }} đ
                </p>

                <div class="border-t border-b border-gray-100 py-4 my-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Mô tả sản phẩm</h3>
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ filled($product->description) ? $product->description : 'Sản phẩm nông sản tươi ngon, đảm bảo an toàn vệ sinh thực phẩm và nguồn gốc rõ ràng.' }}
                    </p>
                    @if($product->origin)
                        <p class="text-sm text-gray-500 mt-3">
                            <span class="font-semibold text-gray-700">Nguồn gốc:</span> {{ $product->origin }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Nút thao tác -->
            <div>
                <div class="flex items-center gap-4 mb-4">
                    <label class="text-xs font-bold text-gray-700 uppercase">Số lượng:</label>
                    <input form="add-to-cart" type="number" name="quantity" value="1" min="1" max="{{ max(1, $product->stock ?? 99) }}" 
                           class="w-20 px-3 py-2 border border-gray-200 rounded-xl text-center text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="flex gap-3">
                    <form id="add-to-cart" action="{{ route('cart.add', $product->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-xl text-sm transition shadow-sm">
                            🛒 Thêm vào giỏ hàng
                        </button>
                    </form>
                    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="h-full bg-red-50 hover:bg-red-100 text-red-500 px-5 rounded-xl text-xl transition" title="Thêm vào yêu thích" aria-label="Thêm vào yêu thích">
                            ❤️
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection