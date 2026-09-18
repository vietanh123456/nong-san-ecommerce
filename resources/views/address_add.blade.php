@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">📍 Thêm địa chỉ nhận hàng</h1>

        <form action="{{ route('address.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Số điện thoại</label>
                <input type="text" name="phone" value="{{ Auth::user()->phone ?? '' }}" required placeholder="0987654321" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Địa chỉ chi tiết</label>
                <textarea name="address" rows="3" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-600">{{ Auth::user()->address ?? '' }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="/profile" class="w-1/2 text-center py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl text-sm hover:bg-gray-200 transition">Hủy</a>
                <button type="submit" class="w-1/2 py-3 bg-emerald-600 text-white font-semibold rounded-xl text-sm hover:bg-emerald-700 transition">Lưu địa chỉ</button>
            </div>
        </form>
    </div>
</div>
@endsection