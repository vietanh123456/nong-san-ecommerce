<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email mới - Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10">
    <main class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <a href="{{ route('profile') }}" class="text-sm font-semibold text-emerald-700 hover:underline">
            &larr; Quay lại hồ sơ
        </a>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Xác thực email mới</h1>
        <p class="mt-2 text-sm leading-6 text-gray-600">
            Mã OTP đã được gửi đến
            <strong class="break-all text-gray-800">{{ $pendingEmail }}</strong>.
            Mã có hiệu lực trong 5 phút. Email tài khoản chỉ được cập nhật sau khi xác thực thành công.
        </p>

        @if (session('success'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('profile.email.verify.submit') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="otp" class="mb-1 block text-sm font-semibold text-gray-700">Mã OTP 6 chữ số</label>
                <input
                    id="otp"
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    required
                    autocomplete="one-time-code"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-center text-lg tracking-[0.4em] focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
            <button
                type="submit"
                class="w-full rounded-lg bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800"
            >
                Xác thực email
            </button>
        </form>

        <form action="{{ route('profile.email.verify.resend') }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="w-full rounded-lg border border-emerald-700 px-4 py-3 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
                Gửi lại mã OTP
            </button>
        </form>
    </main>
</body>
</html>
