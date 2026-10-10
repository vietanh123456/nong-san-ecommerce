<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10">
    <main class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-700 hover:underline">
            &larr; Quay lại đăng nhập
        </a>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Quên mật khẩu?</h1>
        <p class="mt-2 text-sm text-gray-600">
            Nhập email tài khoản. Nếu email đã đăng ký, chúng tôi sẽ gửi mã OTP có hiệu lực trong 5 phút.
        </p>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.otp.send') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', request('email')) }}"
                    required
                    autocomplete="email"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
            <button
                type="submit"
                class="w-full rounded-lg bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800"
            >
                Gửi mã OTP
            </button>
        </form>
    </main>
</body>
</html>
