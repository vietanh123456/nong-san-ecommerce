@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        🛒 Giỏ hàng của bạn
    </h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (empty($cart))
        <div class="bg-white p-8 text-center rounded-xl shadow-sm border border-gray-100">
            <p class="text-gray-500 mb-4">
                Giỏ hàng của bạn đang trống.
            </p>

            <a
                href="{{ route('home') }}"
                class="inline-block bg-emerald-600 text-white px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition"
            >
                ← Tiếp tục mua hàng
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50 text-gray-600 text-sm">
                            <th class="px-5 py-4">Sản phẩm</th>
                            <th class="px-5 py-4">Phân loại</th>
                            <th class="px-5 py-4">Giá</th>
                            <th class="px-5 py-4 text-center">
                                Số lượng
                            </th>
                            <th class="px-5 py-4 text-right">
                                Thành tiền
                            </th>
                            <th class="px-5 py-4 text-center">
                                Hành động
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $total = 0;
                        @endphp

                        @foreach ($cart as $variantId => $item)
                            @php
                                $subtotal =
                                    $item['price'] *
                                    $item['quantity'];

                                $total += $subtotal;
                            @endphp

                            <tr class="border-b last:border-0 hover:bg-gray-50/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if (!empty($item['image']))
                                            <img
                                                src="{{ asset('storage/' . $item['image']) }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-14 h-14 rounded-lg object-cover border border-gray-100"
                                            >
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-emerald-50 flex items-center justify-center text-2xl">
                                                🥑
                                            </div>
                                        @endif

                                        <div>
                                            <a
                                                href="{{ route('products.show', $item['product_id']) }}"
                                                class="font-bold text-gray-800 hover:text-emerald-600"
                                            >
                                                {{ $item['name'] }}
                                            </a>

                                            <p class="text-xs text-gray-400 mt-1">
                                                SKU:
                                                {{ $item['sku'] ?? 'Không có' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex bg-emerald-50 text-emerald-700 text-sm font-semibold px-3 py-1.5 rounded-lg">
                                        {{ $item['variant_name'] }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ number_format($item['price'], 0, ',', '.') }} đ
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-1 border border-gray-300 rounded-lg p-1 bg-gray-50">
                                        <form
                                            action="{{ route('cart.update', $variantId) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="decrease"
                                            >

                                            <button
                                                type="submit"
                                                class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100"
                                                title="Giảm số lượng"
                                            >
                                                −
                                            </button>
                                        </form>

                                        <span class="w-10 text-center font-bold text-gray-800 text-sm">
                                            {{ $item['quantity'] }}
                                        </span>

                                        <form
                                            action="{{ route('cart.update', $variantId) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="increase"
                                            >

                                            <button
                                                type="submit"
                                                class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100"
                                                title="Tăng số lượng"
                                            >
                                                +
                                            </button>
                                        </form>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right font-bold text-emerald-600">
                                    {{ number_format($subtotal, 0, ',', '.') }} đ
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <form
                                        action="{{ route('cart.remove', $variantId) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="text-red-500 hover:text-red-700 text-sm font-semibold"
                                            onclick="return confirm('Xóa phân loại này khỏi giỏ hàng?')"
                                        >
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <a
                    href="{{ route('home') }}"
                    class="text-gray-600 hover:text-emerald-600 hover:underline text-sm font-medium"
                >
                    ← Tiếp tục mua hàng
                </a>

                <div class="text-right">
                    <p class="text-lg font-bold text-gray-700">
                        Tổng tiền:

                        <span class="text-2xl text-emerald-600 font-extrabold">
                            {{ number_format($total, 0, ',', '.') }} đ
                        </span>
                    </p>

                    <button
                        type="button"
                        class="mt-3 bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-emerald-700 transition"
                    >
                        Thanh toán
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection