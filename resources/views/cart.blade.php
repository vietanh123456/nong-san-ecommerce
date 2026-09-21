@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- ================================
        TIÊU ĐỀ
    ================================= --}}
    <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
        🛒 Giỏ hàng của bạn
    </h1>

    {{-- ================================
        THÔNG BÁO THÀNH CÔNG
    ================================= --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================================
        THÔNG BÁO LỖI
    ================================= --}}
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ================================
        GIỎ HÀNG TRỐNG
    ================================= --}}
    @if(empty($cart) || count($cart) == 0)

        <div class="bg-white p-8 text-center rounded-xl shadow-sm border border-gray-100">

            <p class="text-gray-500 mb-4">
                Giỏ hàng của bạn đang trống.
            </p>

            <a href="{{ route('home') }}"
               class="inline-block bg-emerald-600 text-white px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition">
                ← Tiếp tục mua hàng
            </a>

        </div>

    @else

        {{-- ================================
            DANH SÁCH SẢN PHẨM
        ================================= --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

            <table class="w-full text-left border-collapse">

                <thead>
                    <tr class="border-b text-gray-600 text-sm">

                        <th class="py-3">
                            Sản phẩm
                        </th>

                        <th class="py-3">
                            Giá
                        </th>

                        <th class="py-3 text-center">
                            Số lượng
                        </th>

                        <th class="py-3 text-right">
                            Thành tiền
                        </th>

                        <th class="py-3 text-center">
                            Hành động
                        </th>

                    </tr>
                </thead>

                <tbody>

                    @php
                        $total = 0;
                    @endphp

                    @foreach($cart as $id => $item)

                        @php
                            $price = (float) ($item['price'] ?? 0);
                            $quantity = (int) ($item['quantity'] ?? 0);

                            $subtotal = $price * $quantity;

                            $total += $subtotal;
                        @endphp

                        <tr class="border-b last:border-0 hover:bg-gray-50/50">

                            {{-- TÊN SẢN PHẨM --}}
                            <td class="py-4 font-semibold text-gray-800">

                                {{ $item['name'] ?? 'Sản phẩm' }}

                            </td>

                            {{-- GIÁ --}}
                            <td class="py-4 text-gray-600">

                                {{ number_format(
                                    $price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ

                            </td>

                            {{-- ================================
                                SỐ LƯỢNG
                            ================================= --}}
                            <td class="py-4 text-center">

                                <div class="inline-flex items-center gap-1 border border-gray-300 rounded-lg p-1 bg-gray-50">

                                    {{-- GIẢM SỐ LƯỢNG --}}
                                    <form
                                        action="{{ route('cart.update', $id) }}"
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
                                            class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100"
                                        >
                                            -
                                        </button>

                                    </form>

                                    {{-- SỐ LƯỢNG HIỆN TẠI --}}
                                    <span class="w-8 text-center font-bold text-gray-800 text-sm">

                                        {{ $quantity }}

                                    </span>

                                    {{-- TĂNG SỐ LƯỢNG --}}
                                    <form
                                        action="{{ route('cart.update', $id) }}"
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
                                            class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 rounded text-gray-700 font-bold hover:bg-gray-100"
                                        >
                                            +
                                        </button>

                                    </form>

                                </div>

                            </td>

                            {{-- THÀNH TIỀN --}}
                            <td class="py-4 text-right font-bold text-emerald-600">

                                {{ number_format(
                                    $subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ

                            </td>

                            {{-- ================================
                                XÓA SẢN PHẨM
                            ================================= --}}
                            <td class="py-4 text-center">

                                <form
                                    action="{{ route('cart.remove', $id) }}"
                                    method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-red-500 hover:text-red-700 text-sm font-medium"
                                    >
                                        Xóa
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            {{-- ================================
                TỔNG TIỀN + CHECKOUT
            ================================= --}}
            <div class="mt-6 pt-4 border-t flex justify-between items-center">

                {{-- TIẾP TỤC MUA HÀNG --}}
                <a
                    href="{{ route('home') }}"
                    class="text-gray-600 hover:underline text-sm"
                >
                    ← Tiếp tục mua hàng
                </a>

                <div class="text-right">

                    {{-- TỔNG TIỀN --}}
                    <p class="text-lg font-bold text-gray-700">

                        Tổng tiền:

                        <span class="text-2xl text-emerald-600 font-extrabold">

                            {{ number_format(
                                $total,
                                0,
                                ',',
                                '.'
                            ) }}đ

                        </span>

                    </p>

                    {{-- ================================
                        NÚT THANH TOÁN
                    ================================= --}}
                    <a
                        href="{{ route('checkout.index') }}"
                        class="inline-block mt-3 bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-emerald-700 transition"
                    >
                        Thanh toán
                    </a>

                </div>

            </div>

        </div>

    @endif

</div>
@endsection