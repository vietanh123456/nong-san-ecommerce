@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .admin-console { --ink:#f8fafc; --muted:#94a3b8; --panel:#1b2336; --line:rgba(148,163,184,.22); --accent:#4ade80; font-family:'Fira Sans',sans-serif; color:var(--ink); }
    .admin-console h1,.admin-console h2,.admin-console h3 { font-family:'Fira Code',monospace; }
    .admin-shell { background:radial-gradient(circle at 6% 0%,#263c4c 0,transparent 30rem),linear-gradient(135deg,#0f172a,#172033 50%,#0f172a); }
    .admin-panel { background:rgba(27,35,54,.88); border:1px solid var(--line); box-shadow:0 18px 45px rgba(2,6,23,.2); backdrop-filter:blur(12px); }
    .admin-nav-link,.admin-action { border:1px solid #475569; transition:background-color .18s ease,border-color .18s ease,transform .18s ease; }
    .admin-nav-link:hover,.admin-action:hover { background:rgba(74,222,128,.09); border-color:rgba(74,222,128,.55); transform:translateY(-1px); }
    .admin-nav-link[aria-current="page"] { color:#bbf7d0; border-color:#4ade80; background:rgba(74,222,128,.12); }
    .admin-console a:focus-visible,.admin-console button:focus-visible,.admin-console select:focus-visible,.admin-console input:focus-visible { outline:3px solid #f8fafc; outline-offset:3px; }
    .admin-console select,.admin-console input { min-height:42px; color:#f8fafc; background:#0f172a; border:1px solid #475569; border-radius:.55rem; padding:.5rem .7rem; }
    .admin-console select option { color:#0f172a; background:#fff; }
    .admin-console button { cursor:pointer; }
    @media (prefers-reduced-motion:reduce) { .admin-nav-link,.admin-action { transition:none; } .admin-nav-link:hover,.admin-action:hover { transform:none; } }
</style>
@endpush

@section('content')
<div class="admin-shell admin-console min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-7 sm:py-10">
        <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex text-xs uppercase tracking-[.18em] text-emerald-300 font-semibold">Trung tâm điều hành</a>
                <p class="text-sm text-slate-400 mt-2">Quản trị sàn Nông Sản Việt</p>
            </div>
            <nav aria-label="Điều hướng quản trị" class="grid grid-cols-2 sm:flex gap-2">
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>Tài khoản</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.products.index') }}" @if(request()->routeIs('admin.products.*')) aria-current="page" @endif>Sản phẩm</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.certificates.index') }}" @if(request()->routeIs('admin.certificates.*')) aria-current="page" @endif>Chứng nhận</a>
                <a class="admin-nav-link rounded-lg px-3 py-2 text-sm font-semibold text-center" href="{{ route('admin.reviews.index') }}" @if(request()->routeIs('admin.reviews.*')) aria-current="page" @endif>Đánh giá</a>
            </nav>
        </header>

        @yield('admin-content')
    </div>
</div>
@endsection
