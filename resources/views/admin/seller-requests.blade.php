@extends('layouts.admin')

@section('title', 'Duyệt yêu cầu Người bán')

@section('admin-content')
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="mb-6">
            <p class="text-sm font-semibold text-emerald-700">QUẢN TRỊ</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Yêu cầu Người bán</h1>
            <p class="mt-2 text-sm text-gray-600">
                Xem thông tin cửa hàng và duyệt hoặc từ chối yêu cầu đang chờ.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-5 py-4">Người đăng ký</th>
                            <th class="px-5 py-4">Cửa hàng</th>
                            <th class="px-5 py-4">Điện thoại</th>
                            <th class="px-5 py-4">Địa chỉ</th>
                            <th class="px-5 py-4">Mô tả</th>
                            <th class="px-5 py-4">Ngày gửi</th>
                            <th class="px-5 py-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($requests as $sellerRequest)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-gray-900">{{ $sellerRequest->user->name }}</p>
                                    <p class="mt-1 text-gray-500">{{ $sellerRequest->user->email }}</p>
                                </td>
                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $sellerRequest->store_name }}
                                </td>
                                <td class="px-5 py-4">{{ $sellerRequest->phone }}</td>
                                <td class="max-w-xs whitespace-normal px-5 py-4">{{ $sellerRequest->address }}</td>
                                <td class="max-w-xs whitespace-normal px-5 py-4">{{ $sellerRequest->description }}</td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    {{ $sellerRequest->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <form
                                            action="{{ route('admin.seller-requests.approve', $sellerRequest) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-emerald-700 px-3 py-2 font-semibold text-white hover:bg-emerald-800"
                                            >
                                                Duyệt
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('admin.seller-requests.reject', $sellerRequest) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-red-600 px-3 py-2 font-semibold text-white hover:bg-red-700"
                                            >
                                                Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                                    Hiện không có yêu cầu đăng ký người bán đang chờ duyệt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $requests->links() }}
        </div>
    </section>
@endsection
