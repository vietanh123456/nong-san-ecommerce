@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .admin-console { --ink:#1f2937; --muted:#6b7280; --panel:#ffffff; --line:#e5e7eb; --accent:#16a34a; font-family:'Fira Sans',sans-serif; color:var(--ink); }
    .admin-console h1,.admin-console h2,.admin-console h3 { font-family:'Fira Code',monospace; }
    .admin-shell { background:#f3f4f6; }
    .admin-panel { background:var(--panel); border:1px solid var(--line); box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .admin-nav-link,.admin-action { border:1px solid #d1d5db; transition:background-color .18s ease,border-color .18s ease,transform .18s ease; }
    .admin-nav-link:hover,.admin-action:hover { background:#dcfce7; border-color:#16a34a; transform:translateY(-1px); }
    .admin-nav-link[aria-current="page"] { color:#166534; border-color:#16a34a; background:#dcfce7; }
    .admin-console a:focus-visible,.admin-console button:focus-visible,.admin-console select:focus-visible,.admin-console input:focus-visible { outline:3px solid #166534; outline-offset:3px; }
    .admin-console select,.admin-console input { min-height:42px; color:#1f2937; background:#ffffff; border:1px solid #d1d5db; border-radius:.55rem; padding:.5rem .7rem; }
    .admin-console select option { color:#1f2937; background:#fff; }
    .admin-console button { cursor:pointer; }
    @media (prefers-reduced-motion:reduce) { .admin-nav-link,.admin-action { transition:none; } .admin-nav-link:hover,.admin-action:hover { transform:none; } }
</style>
@endpush

@section('content')
<div class="admin-shell admin-console min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-7 sm:py-10">
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex text-xs uppercase tracking-[.18em] text-emerald-700 font-semibold">Trung tâm điều hành</a>
                <p class="text-sm text-gray-500 mt-2">Quản trị sàn Nông Sản Việt</p>
            </div>
            <nav aria-label="Điều hướng quản trị" class="grid grid-cols-2 sm:flex gap-2">
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>Tài khoản</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.products.index') }}" @if(request()->routeIs('admin.products.*')) aria-current="page" @endif>Sản phẩm</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.certificates.index') }}" @if(request()->routeIs('admin.certificates.*')) aria-current="page" @endif>Chứng nhận</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.reviews.index') }}" @if(request()->routeIs('admin.reviews.*')) aria-current="page" @endif>Đánh giá</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.statistics.index') }}" @if(request()->routeIs('admin.statistics.*')) aria-current="page" @endif>Báo cáo doanh thu</a>
            </nav>
        </header>

        @yield('admin-content')
    </div>
</div>
@endsection
