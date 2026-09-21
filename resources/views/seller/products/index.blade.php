<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller - Quản lý sản phẩm</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="bg-[#0e5c36] text-white px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div><p class="text-xs uppercase tracking-widest text-emerald-200">Khu vực seller</p><h1 class="text-xl font-bold">Quản lý sản phẩm</h1></div>
            <div class="flex items-center gap-4 text-sm"><a href="{{ route('home') }}" class="hover:underline">Trang chủ</a><a href="{{ route('profile') }}" class="hover:underline">Hồ sơ</a></div>
        </div>
    </header>
    <main class="max-w-6xl mx-auto px-4 py-8">
        @if(session('success'))<div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
        <div class="mb-6 flex items-center justify-between"><div><h2 class="text-2xl font-bold">Sản phẩm của bạn</h2><p class="mt-1 text-sm text-slate-500">{{ $products->count() }} sản phẩm</p></div><a href="{{ route('seller.products.create') }}" class="rounded-xl bg-[#0e5c36] px-4 py-3 text-sm font-semibold text-white hover:bg-[#0a4528]">+ Thêm sản phẩm</a></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-4">Tên sản phẩm</th><th class="px-5 py-4">Giá</th><th class="px-5 py-4">Tồn kho</th><th class="px-5 py-4">Trạng thái</th><th class="px-5 py-4 text-right">Thao tác</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($products as $product)
                <tr><td class="px-5 py-4 font-semibold">{{ $product->name }}</td><td class="px-5 py-4 text-emerald-700">{{ number_format($product->price, 0, ',', '.') }}đ</td><td class="px-5 py-4">{{ $product->stock }}</td><td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs {{ $product->status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $product->status ? 'Đang bán' : 'Tạm ẩn' }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('seller.products.edit', $product) }}" class="font-semibold text-emerald-700">Sửa</a><form method="POST" action="{{ route('seller.products.toggle', $product) }}">@csrf @method('PATCH')<button class="text-slate-500">{{ $product->status ? 'Ẩn' : 'Hiện' }}</button></form><form method="POST" action="{{ route('seller.products.destroy', $product) }}" onsubmit="return confirm('Xóa sản phẩm này?')">@csrf @method('DELETE')<button class="text-red-500">Xóa</button></form></div></td></tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Bạn chưa có sản phẩm nào.</td></tr>
            @endforelse
            </tbody></table></div>
        </div>
    </main>
</body>
</html>
