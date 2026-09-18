@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        ❤️ Danh Sách Yêu Thích
    </h1>

    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($wishlists->isEmpty())
        <div class="bg-white p-8 rounded-xl text-center shadow-sm">
            <p class="text-gray-500 mb-4">Bạn chưa lưu sản phẩm nào vào danh sách yêu thích.</p>
            <a href="{{ route('home') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700 transition">
                Khám phá sản phẩm ngay
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($wishlists as $item)
                @if($item->product)
                    <div class="bg-white rounded-xl shadow-sm border p-4 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="text-5xl text-center py-4">🥑</div>
                            <h3 class="font-bold text-gray-800 mb-1">{{ $item->product->name }}</h3>
                            <p class="text-emerald-600 font-bold mb-4">{{ number_format($item->product->price) }}đ</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('products.show', $item->product->id) }}" class="flex-1 bg-emerald-600 text-white text-center py-2 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                                Xem
                            </a>
                            <form action="{{ route('wishlist.toggle', $item->product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-red-100 text-red-600 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-red-200 transition">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection