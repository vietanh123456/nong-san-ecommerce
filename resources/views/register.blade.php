<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Sàn Nông Sản</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f4f7f5] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[1000px] bg-white rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-2 min-h-[650px]">
        
        <!-- CỘT BÊN TRÁI: BANNER -->
        <div class="bg-[#0e5c36] text-white p-10 flex flex-col justify-between relative overflow-hidden min-h-[500px] lg:min-h-auto">
            <div class="relative z-10">
                <div class="w-12 h-12 border border-white/30 rounded-xl flex items-center justify-center font-bold text-sm mb-12 tracking-wider">
                    MN
                </div>
                <span class="text-[11px] uppercase tracking-[0.2em] text-emerald-300 font-semibold block mb-3">TÀI KHOẢN MỚI</span>
                <h1 class="text-4xl lg:text-[42px] font-serif font-semibold leading-[1.15]">Bắt đầu ăn lành,<br>sống xanh.</h1>
                <p class="mt-6 text-emerald-100/75 text-sm leading-relaxed max-w-[320px]">
                    Tạo tài khoản ngay để mua sắm nông sản tươi ngon và nhận ưu đãi riêng biệt từ các vùng miền.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-2.5 text-xs text-emerald-200 mt-8">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
                <span><strong class="text-white font-semibold">100% tươi mới</strong> - Nguồn gốc minh bạch</span>
            </div>

            <div class="absolute -bottom-16 -right-16 w-80 h-80 bg-emerald-700/20 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- CỘT BÊN PHẢI: FORM ĐĂNG KÝ -->
        <div class="p-8 lg:p-12 flex flex-col justify-center bg-white">
            <span class="text-[11px] uppercase tracking-[0.15em] text-gray-400 font-semibold block mb-1">THAM GIA CÙNG NHÓM</span>
            <h2 class="text-3xl font-serif font-bold text-slate-900 mb-1">Đăng ký</h2>
            <p class="text-xs text-slate-400 mb-6">Tạo tài khoản chỉ trong vài giây.</p>

            <!-- Tab chuyển đổi -->
            <div class="flex border-b border-gray-100 mb-6">
                <a href="{{ route('login') }}" class="pb-3 text-gray-400 hover:text-gray-600 text-xs font-medium tracking-wide mr-6">Đăng nhập</a>
                <a href="{{ route('register') }}" class="pb-3 border-b-2 border-[#0e5c36] text-[#0e5c36] font-bold text-xs tracking-wide">Đăng ký</a>
            </div>

            <!-- Hiển thị lỗi Validation nếu có -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">HỌ VÀ TÊN</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nguyễn Văn A" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#0e5c36] placeholder:text-gray-300">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">EMAIL</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="ban@example.com" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#0e5c36] placeholder:text-gray-300">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">MẬT KHẨU</label>
                    <input type="password" name="password" required placeholder="Tối thiểu 8 ký tự" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#0e5c36] placeholder:text-gray-300">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">XÁC NHẬN MẬT KHẨU</label>
                    <input type="password" name="password_confirmation" required placeholder="Nhập lại mật khẩu" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#0e5c36] placeholder:text-gray-300">
                </div>

                <button type="submit" class="w-full bg-[#0e5c36] hover:bg-[#0a4628] text-white font-semibold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 transition-all shadow-md mt-2">
                    Tạo tài khoản <span class="text-base leading-none">→</span>
                </button>
            </form>
        </div>

    </div>

</body>
</html>