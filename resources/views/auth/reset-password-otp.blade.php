<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Nông Sản Việt</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-10">
    <main class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-emerald-700 hover:underline">
            &larr; Yêu cầu mã OTP mới
        </a>

        <h1 class="mt-6 text-2xl font-bold text-gray-900">Đặt lại mật khẩu</h1>
        <p class="mt-2 text-sm text-gray-600">Nhập mã OTP trong email và tạo mật khẩu mới.</p>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($otpExpiresAt)
            <p
                id="otp-countdown"
                class="mt-3 text-sm font-medium text-emerald-800"
                data-expires-at="{{ $otpExpiresAt->getTimestampMs() }}"
            >
                Mã OTP có hiệu lực trong:
                <span id="otp-timer" class="font-bold tabular-nums">05:00</span>
            </p>
        @endif
        <p id="otp-expired" class="mt-3 text-sm font-semibold text-red-600" @if (! $otpExpired) hidden @endif>
            Mã OTP đã hết hạn
        </p>
        <a
            id="otp-resend"
            href="{{ route('password.request', ['email' => $email]) }}"
            class="mt-3 inline-block text-sm font-semibold text-emerald-700 underline hover:text-emerald-900"
            @if ($otpExpiresAt && ! $otpExpired) hidden @endif
        >
            Gửi lại mã OTP
        </a>

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('password.otp.reset') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email tài khoản</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autocomplete="email"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
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
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm tracking-[0.3em] focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-gray-700">Mật khẩu mới</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-gray-700">
                    Xác nhận mật khẩu mới
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                >
            </div>
            <button
                id="update-password"
                type="submit"
                @disabled(! $otpExpiresAt || $otpExpired)
                class="w-full rounded-lg bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800"
            >
                Cập nhật mật khẩu
            </button>
        </form>
    </main>

    <script>
        (() => {
            const countdown = document.getElementById('otp-countdown');
            const timer = document.getElementById('otp-timer');
            const expiredMessage = document.getElementById('otp-expired');
            const resendLink = document.getElementById('otp-resend');
            const updateButton = document.getElementById('update-password');

            if (!countdown || !timer || !expiredMessage || !resendLink || !updateButton) {
                return;
            }

            const expiresAt = Number(countdown.dataset.expiresAt);

            const updateCountdown = () => {
                const remainingSeconds = Math.max(
                    0,
                    Math.ceil((expiresAt - Date.now()) / 1000)
                );

                if (remainingSeconds === 0) {
                    countdown.hidden = true;
                    expiredMessage.hidden = false;
                    resendLink.hidden = false;
                    updateButton.disabled = true;
                    updateButton.classList.add('cursor-not-allowed', 'opacity-50');
                    return;
                }

                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = remainingSeconds % 60;

                timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            };

            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        })();
    </script>
</body>
</html>
