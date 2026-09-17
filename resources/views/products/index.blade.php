@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">🌾 Danh Sách Nông Sản</h1>
        <p class="text-slate-500 mt-1">Tìm kiếm và lọc nông sản tươi sạch chuẩn VietGAP</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- BỘ LỌC (FILTER) -->
        <aside class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                🔍 Bộ Lọc Sản Phẩm
            </h2>

            <form action="{{ route('products.index') }}" method="GET" class="space-y-6">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <!-- Filter Danh mục -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Danh Mục Nông Sản</label>
                    <select name="category_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Khoảng giá -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Khoảng Giá (VNĐ)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Từ" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Đến" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Sắp xếp -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sắp Xếp Giá</label>
                    <select name="sort" class="w-full rounded-xl border-slate-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Mới nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến Cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá: Cao đến Thấp</option>
                    </select>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 rounded-xl transition text-sm">
                        Áp dụng
                    </button>
                    <a href="{{ route('products.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition text-center">
                        Xóa lọc
                    </a>
                </div>
            </form>
        </aside>

        <!-- DANH SÁCH SẢN PHẨM & TÌM KIẾM -->
        <main class="lg:col-span-3 space-y-6">
            <!-- Ô Tìm Kiếm -->
            <form action="{{ route('products.index') }}" method="GET" class="flex gap-2">
                @if(request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                @endif
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tên nông sản cần tìm (vd: Táo, Dưa hấu, Gạo...)" class="w-full pl-10 pr-4 py-3 rounded-2xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 text-sm shadow-sm">
                    <span class="absolute left-3.5 top-3.5 text-slate-400">🔎</span>
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6 py-3 rounded-2xl transition text-sm shadow-sm">
                    Tìm kiếm
                </button>
            </form>

            <!-- Grid Danh Sách Sản Phẩm -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                            <div class="h-48 bg-slate-100 relative overflow-hidden">
                                <img src="{{ $product->image ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=500' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                            <div class="p-5">
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    {{ $product->category->name ?? 'Nông Sản' }}
                                </span>
                                <h3 class="font-bold text-slate-800 mt-2 text-lg line-clamp-1">{{ $product->name }}</h3>
                                <p class="text-emerald-600 font-extrabold text-xl mt-3">
                                    {{ number_format($product->price) }} <span class="text-xs text-slate-500 font-normal">đ/kg</span>
                                </p>
                                <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                                    <a href="/wishlist" class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-red-500 hover:border-red-200 transition">
                                        ❤️
                                    </a>
                                    <button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs py-2.5 rounded-xl transition">
                                        🛒 Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-white rounded-2xl p-12 text-center border border-slate-100">
                    <span class="text-5xl">🌾</span>
                    <h3 class="text-lg font-bold text-slate-700 mt-4">Không tìm thấy nông sản phù hợp</h3>
                    <p class="text-slate-500 text-sm mt-1">Vui lòng thử tìm kiếm từ khóa khác hoặc xóa bộ lọc.</p>
                </div>
            @endif
        </main>
    </div>
</div>
@endsection