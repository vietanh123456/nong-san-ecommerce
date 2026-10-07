@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="mb-6">
            <p class="text-sm font-semibold text-emerald-700">QUẢN TRỊ</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Quản lý người dùng</h1>
            <p class="mt-2 text-sm text-gray-600">
                Tìm kiếm tài khoản và xem vai trò, thông tin cửa hàng, trạng thái xét duyệt.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:grid-cols-[1fr_220px_auto_auto]">
            <label class="sr-only" for="search">Tìm theo tên, email, điện thoại hoặc cửa hàng</label>
            <input
                id="search"
                name="search"
                type="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Tên, email, điện thoại hoặc cửa hàng"
                class="rounded-xl border border-gray-300 px-4 py-2.5 focus:border-emerald-700 focus:outline-none"
            >
            <label class="sr-only" for="role">Lọc theo vai trò</label>
            <select
                id="role"
                name="role"
                class="rounded-xl border border-gray-300 px-4 py-2.5 focus:border-emerald-700 focus:outline-none"
            >
                <option value="">Tất cả vai trò</option>
                <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                <option value="seller" @selected(($filters['role'] ?? '') === 'seller')>Seller</option>
                <option value="buyer" @selected(($filters['role'] ?? '') === 'buyer')>Người mua</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#0e5c36] px-5 py-2.5 font-semibold text-white hover:bg-[#0a4628]">
                Tìm kiếm
            </button>
            <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-center font-semibold text-gray-700 hover:bg-gray-50">
                Xóa lọc
            </a>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-5 py-4">Tài khoản</th>
                            <th class="px-5 py-4">Liên hệ</th>
                            <th class="px-5 py-4">Cửa hàng</th>
                            <th class="px-5 py-4">Vai trò</th>
                            <th class="px-5 py-4">Trạng thái Seller</th>
                            <th class="px-5 py-4">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                    <p class="mt-1 text-gray-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $user->phone ?: '—' }}</td>
                                <td class="px-5 py-4">{{ $user->latestSellerRequest?->store_name ?: '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    @if ($user->role === 'seller')
                                        <form
                                            action="{{ route('admin.users.demote', $user) }}"
                                            method="POST"
                                            class="mt-2"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700"
                                            >
                                                Chuyển thành Người mua
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($user->latestSellerRequest)
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                                            {{ $user->latestSellerRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $user->latestSellerRequest->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                            {{ $user->latestSellerRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ ucfirst($user->latestSellerRequest->status) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    {{ $user->created_at?->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                    Không tìm thấy tài khoản phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $users->links() }}</div>
    </section>
@endsection
