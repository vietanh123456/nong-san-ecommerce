@extends('layouts.app')

@section('title', 'Báo cáo doanh thu')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@500;600;700&family=Fira+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .admin-console { --ink:#1f2937; --muted:#6b7280; --panel:#ffffff; --line:#e5e7eb; font-family:'Fira Sans',sans-serif; color:var(--ink); }
    .admin-console h1,.admin-console h2,.metric-value { font-family:'Fira Code',monospace; }
    .admin-shell { background:#f3f4f6; }
    .admin-panel { background:rgba(255,255,255,.95); border:1px solid var(--line); box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .admin-link { transition:background-color .18s ease,border-color .18s ease,transform .18s ease; }
    .admin-link:hover { background:#dcfce7; border-color:#16a34a; transform:translateY(-2px); }
    .admin-link:focus-visible,.admin-console a:focus-visible,.admin-console input:focus-visible,.admin-console button:focus-visible { outline:3px solid #166534; outline-offset:3px; }
    .metric-card { min-height:120px; }
    .metric-card::before { content:'';position:absolute;inset:0 auto 0 0;width:4px;background:var(--accent,#16a34a);border-radius:1rem 0 0 1rem; }
    @media (prefers-reduced-motion:reduce) {.admin-link {transition:none}.admin-link:hover {transform:none}}
</style>
@endpush

@section('content')
<div class="admin-shell admin-console min-h-screen"><div class="max-w-7xl mx-auto px-4 sm:px-6 py-7 sm:py-10">
    <header class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6 mb-8">
        <div>
            <p class="uppercase tracking-[.18em] text-xs font-semibold text-emerald-700">Trung tâm điều hành</p>
            <h1 class="text-2xl sm:text-3xl font-bold mt-2 text-gray-800">Báo cáo doanh thu</h1>
            <p class="text-gray-500 mt-2 max-w-2xl">Theo dõi doanh thu, đơn hàng và sản phẩm bán chạy của toàn hệ thống.</p>
        </div>
        <nav aria-label="Điều hướng quản trị" class="grid grid-cols-2 sm:flex gap-2">
            <a class="admin-link rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-center text-gray-700" href="{{ route('admin.dashboard') }}">← Trang chủ</a>
            <a class="admin-link rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-center text-gray-700" href="{{ route('admin.users.index') }}">Tài khoản</a>
            <a class="admin-link rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-center text-gray-700" href="{{ route('admin.products.index') }}">Sản phẩm</a>
        </nav>
    </header>

    <section aria-label="Chỉ số doanh thu" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="admin-panel metric-card relative rounded-2xl p-5" style="--accent:#16a34a">
            <p class="text-sm text-gray-500">Tổng doanh thu</p>
            <p class="metric-value text-2xl sm:text-3xl font-bold mt-5 text-gray-800">{{ number_format($revenue['total'], 0, ',', '.') }}đ</p>
            <p class="text-xs text-gray-500 mt-2">Tích lũy từ đơn hoàn thành</p>
        </div>
        <div class="admin-panel metric-card relative rounded-2xl p-5" style="--accent:#2563eb">
            <p class="text-sm text-gray-500">Doanh thu hôm nay</p>
            <p class="metric-value text-2xl sm:text-3xl font-bold mt-5 text-gray-800">{{ number_format($revenue['today'], 0, ',', '.') }}đ</p>
            <p class="text-xs text-gray-500 mt-2">{{ now()->translatedFormat('d/m/Y') }}</p>
        </div>
        <div class="admin-panel metric-card relative rounded-2xl p-5" style="--accent:#9333ea">
            <p class="text-sm text-gray-500">7 ngày gần nhất</p>
            <p class="metric-value text-2xl sm:text-3xl font-bold mt-5 text-gray-800">{{ number_format($revenue['last_7_days'], 0, ',', '.') }}đ</p>
            <p class="text-xs text-gray-500 mt-2">Tính đến hôm nay</p>
        </div>
        <div class="admin-panel metric-card relative rounded-2xl p-5" style="--accent:#d97706">
            <p class="text-sm text-gray-500">Tháng này</p>
            <p class="metric-value text-2xl sm:text-3xl font-bold mt-5 text-gray-800">{{ number_format($revenue['current_month'], 0, ',', '.') }}đ</p>
            <p class="text-xs text-gray-500 mt-2">{{ now()->translatedFormat('m/Y') }}</p>
        </div>
    </section>

    <section aria-label="Doanh thu theo khoảng ngày tùy chỉnh" class="admin-panel rounded-2xl p-5 mt-5">
        <h2 class="text-lg font-bold text-gray-800">Lọc doanh thu theo khoảng ngày</h2>
        <p class="text-sm text-gray-500 mt-1 mb-4">Chọn ngày bắt đầu và ngày kết thúc để xem doanh thu trong khoảng.</p>
        <form method="GET" action="{{ route('admin.statistics.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label for="from_date" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Từ ngày</label>
                <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded-lg bg-white border border-gray-300 px-3 py-2 text-sm text-gray-800">
            </div>
            <div class="flex-1">
                <label for="to_date" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Đến ngày</label>
                <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded-lg bg-white border border-gray-300 px-3 py-2 text-sm text-gray-800">
            </div>
            <button type="submit" class="admin-link rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">Xem doanh thu</button>
            @if($filters['from_date'] || $filters['to_date'])
                <a href="{{ route('admin.statistics.index') }}" class="admin-link rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-center text-gray-700">Xóa lọc</a>
            @endif
        </form>
        @error('from_date')<p class="text-sm text-rose-600 mt-3">{{ $message }}</p>@enderror
        @error('to_date')<p class="text-sm text-rose-600 mt-3">{{ $message }}</p>@enderror
        @if($customRange)
            <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm text-gray-600">Doanh thu từ <span class="font-semibold text-gray-800">{{ $customRange['from_date'] ?? '...' }}</span> đến <span class="font-semibold text-gray-800">{{ $customRange['to_date'] ?? '...' }}</span></p>
                <p class="metric-value text-2xl font-bold mt-2 text-emerald-700">{{ number_format($customRange['revenue'], 0, ',', '.') }}đ</p>
            </div>
        @endif
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <div class="admin-panel rounded-2xl p-5 lg:col-span-2">
            <h2 class="text-lg font-bold text-gray-800">Đơn hàng theo trạng thái</h2>
            <p class="text-sm text-gray-500 mt-1 mb-5">Tổng {{ number_format($totalOrders) }} đơn hàng trong hệ thống.</p>
            @php($statusTotal = max(1, $totalOrders))
            <div class="space-y-4">
                @foreach(['pending'=>['Chờ xử lý','bg-amber-400'],'processing'=>['Đang xử lý','bg-sky-500'],'shipping'=>['Đang giao','bg-indigo-500'],'completed'=>['Hoàn thành','bg-emerald-500'],'cancelled'=>['Đã hủy','bg-rose-500']] as $key => [$label,$color])
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-semibold text-gray-800">{{ number_format($ordersByStatus[$key]) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-full rounded-full {{ $color }}" style="width:{{ ($ordersByStatus[$key] / $statusTotal) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="admin-panel metric-card relative rounded-2xl p-5" style="--accent:#2563eb">
            <p class="text-sm text-gray-500">Tổng khách hàng</p>
            <p class="metric-value text-3xl font-bold mt-5 text-gray-800">{{ number_format($totalCustomers) }}</p>
            <p class="text-xs text-gray-500 mt-2">Tài khoản vai trò khách hàng</p>
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
        <div class="admin-panel rounded-2xl p-5">
            <h2 class="text-lg font-bold text-gray-800">Top 5 sản phẩm theo doanh thu</h2>
            <p class="text-sm text-gray-500 mt-1 mb-4">Chỉ tính các đơn đã hoàn thành.</p>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[380px] text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="pb-3 font-medium">Sản phẩm</th>
                            <th class="pb-3 font-medium text-right">Số lượng</th>
                            <th class="pb-3 font-medium text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($topProductsByRevenue as $product)
                            <tr>
                                <td class="py-3 font-semibold text-gray-800">{{ $product['product_name'] }}</td>
                                <td class="py-3 text-right text-gray-600">{{ number_format($product['total_quantity']) }}</td>
                                <td class="py-3 text-right text-emerald-700 font-semibold">{{ number_format($product['total_revenue'], 0, ',', '.') }}đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-gray-500">Chưa có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="admin-panel rounded-2xl p-5">
            <h2 class="text-lg font-bold text-gray-800">Top 5 sản phẩm theo số lượng bán</h2>
            <p class="text-sm text-gray-500 mt-1 mb-4">Chỉ tính các đơn đã hoàn thành.</p>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[380px] text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="pb-3 font-medium">Sản phẩm</th>
                            <th class="pb-3 font-medium text-right">Số lượng</th>
                            <th class="pb-3 font-medium text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($topProductsByQuantity as $product)
                            <tr>
                                <td class="py-3 font-semibold text-gray-800">{{ $product['product_name'] }}</td>
                                <td class="py-3 text-right text-gray-600">{{ number_format($product['total_quantity']) }}</td>
                                <td class="py-3 text-right text-emerald-700 font-semibold">{{ number_format($product['total_revenue'], 0, ',', '.') }}đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-gray-500">Chưa có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div></div>
@endsection