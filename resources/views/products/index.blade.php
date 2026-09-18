@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6">
    
    <!-- Thanh Tìm Kiếm & Bộ Lọc -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
        <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Từ khóa tìm kiếm -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tìm kiếm sản phẩm</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tên sản phẩm (ví dụ: Bơ, Cam...)" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
            </div>

            <!-- Danh mục -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Danh mục</label>
                <select name="category_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nút lọc -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-sm transition">
                    Tìm kiếm
                </button>
                <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 px-3 rounded-xl text-sm transition" title="Xóa lọc">
                    🔄
                </a>
            </div>
        </form>
    </div>

    <!-- Kết quả tìm kiếm -->
    @if(request('search'))
        <p class="text-sm text-gray-600 mb-4">Kết quả tìm kiếm cho từ khóa: <strong class="text-emerald-700">"{{ request('search') }}"</strong></p>
    @endif

    <!-- Danh sách sản phẩm -->
    @if(count($products) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                    
                    <a href="{{ route('products.show', $product->id) }}" class="block relative bg-gray-50 h-48 overflow-hidden flex items-center justify-center">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-5xl">🥑</span>
                        @endif
                    </a>

                    <div class="p-4 flex-grow flex flex-col justify-between">
                        <div>
                            <a href="{{ route('products.show', $product->id) }}" class="font-bold text-gray-800 text-base mb-1 line-clamp-1 hover:text-emerald-600">
                                {{ $product->name }}
                            </a>
                            <p class="text-emerald-600 font-bold text-lg mb-4">
                                {{ number_format($product->price ?? 0, 0, ',', '.') }} đ
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('products.show', $product->id) }}" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold text-xs py-2.5 rounded-xl transition">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- ĐOẠN PHÂN TRANG ĐÃ ĐƯỢC BẢO VỆ AN TOÀN -->
        @if (method_exists($products, 'links'))
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif

    @else
        <!-- Trạng thái không tìm thấy sản phẩm -->
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 my-6">
            <div class="text-5xl mb-4">🔍</div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Không tìm thấy sản phẩm nào</h3>
            <p class="text-gray-500 text-xs mb-6">Hãy thử lại với từ khóa khác hoặc xóa bộ lọc.</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-6 py-3 rounded-xl transition">
                Xem tất cả sản phẩm
            </a>
        </div>
    @endif

</div>
@endsection