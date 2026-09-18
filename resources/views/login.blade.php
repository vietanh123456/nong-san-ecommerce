<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Sàn Nông Sản</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f4f7f5] min-h-screen flex items-center justify-center p-4">

    <!-- Card chính -->
    <div class="w-full max-w-[1000px] bg-white rounded-[32px] shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        
        <!-- Cột bên trái: BANNER XANH -->
        <div class="bg-[#0e5c36] text-white p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="w-12 h-12 border border-white/30 rounded-xl flex items-center justify-center font-bold text-xl mb-8">
                    MN
                </div>
                <span class="text-[11px] uppercase tracking-[0.2em] text-emerald-300 font-semibold block mb-2">Sàn Nông Sản Việt</span>
                <h1 class="text-4xl lg:text-[42px] font-serif font-semibold leading-[1.15]">Vị tươi lành từ lòng đất Việt</h1>
                <p class="mt-6 text-emerald-100/75 text-sm leading-relaxed max-w-[320px]">
                    Kết nối bạn với những sản phẩm nông nghiệp tử tế, rõ nguồn gốc và được tuyển chọn từ các vùng quê Việt.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-2.5 text-xs text-emerald-200 mt-8">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
                <span><strong>100% tươi mới</strong> - Giao tận tay trong ngày</span>
            </div>
        </div>

        <!-- Cột bên phải: FORM ĐĂNG NHẬP -->
        <div class="p-8 sm:p-12 flex flex-col justify-between bg-white">
            <div>
                <!-- Header Form -->
                <div class="mb-8">
                    <span class="text-[11px] uppercase tracking-[0.15em] text-gray-400 font-bold block mb-1">CHÀO MỪNG BẠN</span>
                    <h2 class="text-3xl font-serif font-bold text-gray-900">Đăng nhập</h2>
                    <p class="text-xs text-gray-500 mt-1">Tiếp tục hành trình ăn lành, sống xanh.</p>
                </div>

                <!-- Tab chuyển đổi -->
                <div class="flex border-b border-gray-200 mb-6 text-sm font-semibold">
                    <a href="{{ route('login') }}" class="pb-3 border-b-2 border-[#0e5c36] text-[#0e5c36] mr-6">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="pb-3 text-gray-400 hover:text-gray-600">Đăng ký</a>
                </div>

                <!-- Hiển thị lỗi nếu có -->
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- FORM ĐĂNG NHẬP (ĐÃ CẬP NHẬT ROUTE CHUẨN) -->
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-2">EMAIL</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="yen47575@gmail.com" 
    class="w-full px-4 py-3.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#0e5c36] focus:bg-white focus:outline-none transition">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider">MẬT KHẨU</label>
                            <a href="#" class="text-xs text-[#0e5c36] hover:underline font-semibold">Quên mật khẩu?</a>
                        </div>
                       <input type="password" name="password" required placeholder="••••••••" 
    class="w-full px-4 py-3.5 bg-slate-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#0e5c36] focus:bg-white focus:outline-none transition">
                    </div>

                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="w-4 h-4 text-[#0e5c36] border-gray-300 rounded focus:ring-[#0e5c36]">
                        <label for="remember" class="ml-2 text-xs text-gray-600 font-medium">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" 
                        class="w-full bg-[#0e5c36] hover:bg-[#0a4528] text-white font-semibold py-4 px-6 rounded-xl flex items-center justify-between transition shadow-lg shadow-emerald-900/20">
                        <span>Đăng nhập</span>
                        <span class="text-lg">&rarr;</span>
                    </button>
                </form>
            </div>

            <!-- Footer nhỏ -->
            <div class="mt-8 text-center text-xs text-gray-400">
                Chưa có tài khoản? <a href="{{ route('register') }}" class="text-[#0e5c36] font-bold hover:underline">Tạo tài khoản ngay</a>
            </div>
        </div>

    </div>

</body>
</html>