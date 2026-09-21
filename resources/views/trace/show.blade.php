@extends('layouts.app')

@section('title', 'Truy xuất '.$batch->batch_code)

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4"><div class="bg-white rounded-2xl shadow p-7">
    <p class="text-emerald-700 font-bold">✓ Thông tin truy xuất đã xác thực</p><h1 class="text-3xl font-bold mt-2">{{ $batch->product->name }}</h1><p class="text-gray-500 mt-1">Mã lô: <strong>{{ $batch->batch_code }}</strong></p>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-7 text-sm"><div><dt class="text-gray-500">Xuất xứ</dt><dd class="font-semibold">{{ $batch->origin }}</dd></div><div><dt class="text-gray-500">Nhà sản xuất</dt><dd class="font-semibold">{{ $batch->producer ?? 'Đang cập nhật' }}</dd></div><div><dt class="text-gray-500">Ngày thu hoạch</dt><dd class="font-semibold">{{ $batch->harvest_date?->format('d/m/Y') }}</dd></div><div><dt class="text-gray-500">Ngày đóng gói</dt><dd class="font-semibold">{{ $batch->packaged_date?->format('d/m/Y') }}</dd></div><div><dt class="text-gray-500">Hạn sử dụng</dt><dd class="font-semibold">{{ $batch->expiry_date?->format('d/m/Y') ?? 'Không áp dụng' }}</dd></div></dl>
    <h2 class="text-xl font-bold mt-8">Chứng nhận chất lượng</h2><ul class="mt-3 space-y-2">@forelse($batch->certificates as $certificate)<li><a class="text-emerald-700 underline" target="_blank" href="{{ route('trace.certificates.show', $certificate) }}">{{ $certificate->name }}</a></li>@empty<li class="text-gray-500">Chưa có chứng nhận đã được duyệt.</li>@endforelse</ul>
</div></div>
@endsection
