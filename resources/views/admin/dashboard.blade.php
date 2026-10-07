@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-emerald-700">KHU VỰC QUẢN TRỊ</p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Theo dõi tổng quan và quản lý hoạt động của cửa hàng.
                </p>
            </div>
            <a
                href="{{ route('admin.users.index') }}"
                class="rounded-xl bg-[#0e5c36] px-5 py-3 font-semibold text-white hover:bg-[#0a4628]"
            >
                Quản lý người dùng
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Tổng tài khoản</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($statistics['users']) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Người mua</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($statistics['buyers']) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Người bán</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($statistics['sellers']) }}</p>
            </article>
            <article class="rounded-2xl border border-amber-100 bg-amber-50 p-6 shadow-sm">
                <p class="text-sm text-amber-800">Yêu cầu Seller đang chờ</p>
                <p class="mt-2 text-3xl font-bold text-amber-900">
                    {{ number_format($statistics['pending_seller_requests']) }}
                </p>
                <a
                    href="{{ route('admin.seller-requests.index') }}"
                    class="mt-3 inline-block text-sm font-semibold text-amber-900 underline"
                >
                    Xem yêu cầu
                </a>
            </article>
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Sản phẩm</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($statistics['products']) }}</p>
            </article>
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500">Danh mục</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($statistics['categories']) }}</p>
            </article>
        </div>

        <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Thao tác quản trị</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Tìm kiếm người dùng
                </a>
                <a
                    href="{{ route('admin.seller-requests.index') }}"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Duyệt yêu cầu Seller
                </a>
            </div>
        </div>
    </section>
@endsection
