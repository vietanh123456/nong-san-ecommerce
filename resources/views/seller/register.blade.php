@extends('layouts.app')

@section('title', 'Đăng ký Người bán')

@section('content')
    <section class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="mb-7">
                <p class="text-sm font-semibold text-emerald-700">CÙNG BÁN NÔNG SẢN</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Đăng ký Người bán</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Gửi thông tin cửa hàng để Admin xem xét và phê duyệt.
                </p>
            </div>

            @if ($sellerRequest?->status === 'pending')
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">
                    Yêu cầu đã được gửi, vui lòng chờ Admin duyệt.
                </div>
            @else
                @if ($sellerRequest?->status === 'rejected')
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                        Yêu cầu trước đó chưa được duyệt. Bạn có thể cập nhật thông tin và gửi lại.
                    </div>
                @endif

                <form action="{{ route('seller.register.submit') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="store_name" class="mb-2 block text-sm font-semibold text-gray-700">
                            Tên cửa hàng
                        </label>
                        <input
                            id="store_name"
                            name="store_name"
                            type="text"
                            value="{{ old('store_name', $sellerRequest?->store_name) }}"
                            required
                            maxlength="255"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >
                        @error('store_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">
                            Số điện thoại
                        </label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', $sellerRequest?->phone ?? $user->phone) }}"
                            required
                            maxlength="20"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="mb-2 block text-sm font-semibold text-gray-700">
                            Địa chỉ cửa hàng
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            required
                            maxlength="1000"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >{{ old('address', $sellerRequest?->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                            Mô tả gian hàng
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            required
                            maxlength="2000"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >{{ old('description', $sellerRequest?->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[#0e5c36] px-5 py-3 font-semibold text-white transition hover:bg-[#0a4628]"
                    >
                        Gửi yêu cầu đăng ký
                    </button>
                </form>
            @endif
        </div>
    </section>
@endsection
