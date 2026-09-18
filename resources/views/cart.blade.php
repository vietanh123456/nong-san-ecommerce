@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        🛒 Giỏ hàng của bạn
    </h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if(empty($cart) || count($cart) == 0)
        <div class="bg-white p-8 text-center rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 mb-4">Giỏ hàng của bạn đang trống.</p>
            <a href="/home" class="inline-block bg-emerald-600 text-white px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                ← Tiếp tục mua hàng
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-600 text-sm pb-3">
                        <th class="py-3">Sản phẩm</th>
                        <th class="py-3">Giá</th>
                        <th class="py-3 text-center">Số lượng</th>
                        <th class="py-3 text-right">Thành tiền</th>
                        <th class="py-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($cart as $id => $item)
                        @php 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        @endphp
                        <tr class="border-b last:border-0 hover:bg-gray-50/50">
                            <td class="py-4 font-semibold text-gray-800">
                                {{ $item['name'] }}
                            </td>
                            <td class="py-4 text-gray-600">
                                {{ number_format($item['price'], 0, ',', '.') }}đ
                            </td>
                            
                            <!-- Cột tăng/giảm số lượng -->
                            <td class="py-4 text-center">
                                <div class="inline-flex items-center gap-1 border border-gray-300 rounded-lg p-1 bg-gray-50">
                                    <!-- Nút Giảm -->
                                    <form action="{{ route('cart.update', $id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="decrease">
                                        <button type="submit" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100">-</button>
                                    </form>

                                    <!-- Hiển thị số lượng -->
                                    <span class="w-8 text-center font-bold text-gray-800 text-sm">{{ $item['quantity'] }}</span>

                                    <!-- Nút Tăng -->
                                    <form action="{{ route('cart.update', $id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="increase">
                                        <button type="submit" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100">+</button>
                                    </form>
                                </div>
                            </td>

                            <td class="py-4 text-right font-bold text-emerald-600">
                                {{ number_format($subtotal, 0, ',', '.') }}đ
                            </td>
                            <td class="py-4 text-center">
                                <form action="{{ route('cart.remove', $id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 pt-4 border-t flex justify-between items-center">
                <a href="/home" class="text-gray-600 hover:underline text-sm">← Tiếp tục mua hàng</a>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-700">Tổng tiền: <span class="text-2xl text-emerald-600 font-extrabold">{{ number_format($total, 0, ',', '.') }}đ</span></p>
                    <button class="mt-3 bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-emerald-700 transition">Thanh toán</button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection