<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-[#0e5c36] text-white py-4 px-6 flex justify-between items-center shadow-md">
        <a href="/" class="text-xl font-bold flex items-center gap-2">
            🌱 <span>Nông Sản Việt</span>
        </a>
        <a href="/" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-lg transition">
            &larr; Quay lại Trang chủ
        </a>
    </header>

    <div class="max-w-4xl mx-auto py-10 px-4 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Cột trái: Avatar & Thông tin tổng quan -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 bg-emerald-100 text-[#0e5c36] font-bold text-2xl rounded-full flex items-center justify-center mx-auto mb-4">
                {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
            </div>
            <h3 class="font-bold text-gray-800 text-lg">{{ Auth::user()->name ?? 'Người dùng' }}</h3>
            <p class="text-xs text-gray-500 mb-4">{{ Auth::user()->email ?? 'email@example.com' }}</p>
            @if(Auth::user()->isSeller())
                <span class="inline-block bg-emerald-50 text-[#0e5c36] text-xs font-semibold px-3 py-1 rounded-full">Influencer</span>
            @elseif(Auth::user()->isSellerPending())
                <span class="inline-block bg-amber-50 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full">Đang chờ duyệt</span>
            @elseif(Auth::user()->isAdmin())
                <span class="inline-block bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full">Quản trị viên</span>
            @else
                <span class="inline-block bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">Tài khoản khách hàng</span>
                <form action="{{ route('profile.seller-request') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Đăng ký bán hàng</button>
                </form>
            @endif
        </div>

        <!-- Cột phải: Form cập nhật thông tin & Danh sách địa chỉ -->
        <div class="md:col-span-2 space-y-6">
            
            <!-- Thông báo thành công nếu có -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Đổi tên người dùng -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="font-bold text-gray-800 border-b pb-3 mb-4 flex items-center gap-2">
                    👤 <span>Thông tin tài khoản</span>
                </h4>
                
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">HỌ VÀ TÊN</label>
                        <input type="text" name="name" value="{{ Auth::user()->name ?? '' }}" required
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0e5c36]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">SỐ ĐIỆN THOẠI</label>
                        <input type="text" name="phone" value="{{ $user->phone ?? '' }}"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0e5c36]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">EMAIL (Không thể sửa)</label>
                        <input type="email" value="{{ Auth::user()->email ?? '' }}" disabled
                            class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 cursor-not-allowed">
                    </div>
                    <button type="submit" class="bg-[#0e5c36] hover:bg-[#0a4528] text-white text-xs font-semibold px-5 py-2.5 rounded-lg transition">
                        Lưu thay đổi
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="font-bold text-gray-800 border-b pb-3 mb-4">🔒 Đổi mật khẩu</h4>
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="password" name="current_password" required placeholder="Mật khẩu hiện tại" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                    <input type="password" name="password" required minlength="8" placeholder="Mật khẩu mới (ít nhất 8 ký tự)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                    <input type="password" name="password_confirmation" required placeholder="Nhập lại mật khẩu mới" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                    <button type="submit" class="bg-[#0e5c36] hover:bg-[#0a4528] text-white text-xs font-semibold px-5 py-2.5 rounded-lg transition">Đổi mật khẩu</button>
                </form>
            </div>

            <!-- Khối Danh sách Địa chỉ nhận hàng -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h4 class="font-bold text-gray-800 flex items-center gap-2">
                        📍 <span>Địa chỉ nhận hàng</span>
                    </h4>
                    <button type="button" onclick="openModal()" class="text-xs text-[#0e5c36] font-semibold hover:underline">
                        + Thêm địa chỉ mới
                    </button>
                </div>
                
                <!-- Vòng lặp hiển thị danh sách từ CSDL -->
                @if(isset($addresses) && count($addresses) > 0)
                    <div class="space-y-3">
                        @foreach($addresses as $addr)
                            <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-gray-800">{{ $addr->recipient_name }}</span>
                                        <span class="text-xs text-gray-500">| {{ $addr->phone }}</span>
                                        @if($addr->is_default)
                                            <span class="text-[10px] bg-[#0e5c36] text-white px-2 py-0.5 rounded">Mặc định</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">{{ $addr->address_detail }}</p>
                                </div>
                                <div class="flex gap-3 mt-3 text-xs">
                                    <details>
                                        <summary class="cursor-pointer text-[#0e5c36] font-semibold">Sửa</summary>
                                        <form action="{{ route('address.update', $addr) }}" method="POST" class="grid gap-2 mt-3">
                                            @csrf @method('PUT')
                                            <input name="recipient_name" value="{{ $addr->recipient_name }}" required class="px-3 py-2 border rounded-lg">
                                            <input name="phone" value="{{ $addr->phone }}" required class="px-3 py-2 border rounded-lg">
                                            <textarea name="address_detail" required class="px-3 py-2 border rounded-lg">{{ $addr->address_detail }}</textarea>
                                            <label><input type="checkbox" name="is_default" value="1" @checked($addr->is_default)> Đặt mặc định</label>
                                            <button class="w-fit px-3 py-2 bg-[#0e5c36] text-white rounded-lg">Lưu địa chỉ</button>
                                        </form>
                                    </details>
                                    @if(!$addr->is_default)
                                        <form action="{{ route('address.default', $addr) }}" method="POST">@csrf @method('PATCH')<button class="text-[#0e5c36] font-semibold">Đặt mặc định</button></form>
                                    @endif
                                    <form action="{{ route('address.destroy', $addr) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 font-semibold">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 text-center py-6">Bạn chưa có địa chỉ nhận hàng nào. Bấm vào nút trên để thêm!</p>
                @endif
            </div>

        </div>

    </div>

    <!-- Popup Modal Thêm địa chỉ mới -->
    <div id="addressModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl mx-4">
            <h3 class="font-bold text-gray-800 mb-4 text-base">Thêm địa chỉ nhận hàng</h3>
            <form action="{{ route('address.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">TÊN NGƯỜI NHẬN</label>
                    <input type="text" name="recipient_name" required placeholder="Nhập tên người nhận" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0e5c36]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">SỐ ĐIỆN THOẠI</label>
                    <input type="text" name="phone" required placeholder="Nhập số điện thoại" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0e5c36]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">ĐỊA CHỈ CHI TIẾT</label>
                    <input type="text" name="address_detail" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0e5c36]">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" id="default_chk" class="rounded text-[#0e5c36]">
                    <label for="default_chk" class="text-xs text-gray-600 cursor-pointer">Đặt làm địa chỉ mặc định</label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-300">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-[#0e5c36] text-white text-xs font-semibold rounded-lg hover:bg-[#0a4528]">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script bật/tắt Modal -->
    <script>
        function openModal() { 
            const modal = document.getElementById('addressModal');
            modal.classList.remove('hidden'); 
            modal.classList.add('flex'); 
        }
        function closeModal() { 
            const modal = document.getElementById('addressModal');
            modal.classList.remove('flex'); 
            modal.classList.add('hidden'); 
        }
    </script>

</body>
</html>