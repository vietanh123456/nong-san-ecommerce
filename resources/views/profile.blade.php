@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân - Nông Sản Việt')

@section('content')
    @php
        $emailFormOpen = $user->pending_email !== null
            || $errors->has('email')
            || $errors->has('current_password');
    @endphp

    <div class="mx-auto grid max-w-6xl grid-cols-1 items-start gap-6 px-4 py-8 sm:px-6 lg:grid-cols-3 lg:px-8">
        <aside class="self-start rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-2xl font-bold text-emerald-800">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <h1 class="break-words text-lg font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="mt-1 break-all text-sm text-gray-600">{{ $user->email }}</p>
            <a
                href="{{ route('home') }}"
                class="mt-5 inline-flex items-center justify-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-900"
            >
                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7M3 12h18" />
                </svg>
                Về trang chủ
            </a>
        </aside>

        <div class="min-w-0 space-y-6 lg:col-span-2">
            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM5 21a7 7 0 0114 0H5z" />
                    </svg>
                    <h2 class="text-lg font-bold text-gray-900">Thông tin cá nhân</h2>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4" data-loading-form>
                    @csrf
                    <div>
                        <label for="profile-name" class="mb-1.5 block text-sm font-medium text-gray-700">Họ và tên</label>
                        <input
                            id="profile-name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            maxlength="255"
                            autocomplete="name"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button
                        type="submit"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60"
                        data-loading-label="Đang lưu..."
                    >
                        Lưu thông tin
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                    <h2 class="text-lg font-bold text-gray-900">Đổi email</h2>
                </div>

                <div class="mb-4 rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-semibold text-gray-500">Email hiện tại</p>
                    <p class="mt-1 break-all text-sm font-semibold text-gray-900">{{ $user->email }}</p>
                </div>

                @if ($user->pending_email)
                    <div class="mb-5 space-y-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        @error('email')
                            <p class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
                        @enderror

                        <div>
                            <p class="text-xs font-semibold text-amber-900">Email mới chờ xác thực</p>
                            <p class="mt-1 break-all text-sm font-bold text-amber-950">{{ $user->pending_email }}</p>
                        </div>
                        <p class="text-sm leading-6 text-amber-900">
                            Email hiện tại vẫn được sử dụng cho đến khi xác thực email mới thành công.
                        </p>

                        @if ($otpExpiresAt && $otpExpiresAt->isFuture())
                            <p class="text-sm text-amber-900" aria-live="polite">
                                Mã OTP còn hiệu lực:
                                <strong id="otp-expiry-countdown"
        data-seconds="{{ max(0, (int) floor(now()->diffInSeconds($otpExpiresAt))) }}">
</strong>
                                ({{ $otpTtlMinutes }} phút kể từ khi gửi).
                            </p>
                        @else
                            <p class="text-sm font-medium text-amber-900">
                                Mã OTP đã hết hạn hoặc không còn hiệu lực. Hãy gửi lại mã để tiếp tục.
                            </p>
                        @endif

                        @if ($resendAvailableIn > 0)
                            <p class="text-sm text-amber-900" aria-live="polite">
                                Có thể gửi mã mới sau
                                <strong id="resend-countdown" data-seconds="{{ $resendAvailableIn }}"></strong>.
                                Giới hạn gửi là {{ $otpSendLimit }} mã trong {{ $otpSendWindowMinutes }} phút.
                            </p>
                        @else
                            <p class="text-sm text-amber-900">
                                Có thể gửi lại mã ngay. Giới hạn gửi là {{ $otpSendLimit }} mã trong {{ $otpSendWindowMinutes }} phút.
                            </p>
                        @endif

                        <form action="{{ route('profile.email.verify.submit') }}" method="POST" class="space-y-3" data-loading-form>
                            @csrf
                            <div>
                                <label for="email-change-otp" class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Mã xác thực 6 chữ số
                                </label>
                                <input
                                    id="email-change-otp"
                                    type="text"
                                    name="otp"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    required
                                    class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm tracking-[0.25em] focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                >
                                @error('otp')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60"
                                data-loading-label="Đang xác thực..."
                            >
                                Xác thực email
                            </button>
                        </form>

                        <div class="flex flex-col gap-2 border-t border-amber-200 pt-4 sm:flex-row">
                            <form action="{{ route('profile.email.verify.resend') }}" method="POST" class="sm:flex-1" data-loading-form>
                                @csrf
                                <button
                                    type="submit"
                                    @disabled($resendAvailableIn > 0)
                                    class="min-h-10 w-full rounded-xl border border-emerald-700 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50"
                                    data-loading-label="Đang gửi..."
                                >
                                    Gửi lại mã
                                </button>
                            </form>
                            <form
                                action="{{ route('profile.email.cancel') }}"
                                method="POST"
                                class="sm:flex-1"
                                data-loading-form
                                onsubmit="return confirm('Bạn có chắc muốn hủy yêu cầu đổi email?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="min-h-10 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 disabled:cursor-wait disabled:opacity-60"
                                    data-loading-label="Đang hủy..."
                                >
                                    Hủy yêu cầu
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    @if ($errors->has('otp'))
                        <p class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            {{ $errors->first('otp') }}
                        </p>
                    @endif

                    <button
                        id="show-email-change-form"
                        type="button"
                        @class([
                            'hidden' => $emailFormOpen,
                            'inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800',
                        ])
                    >
                        Đổi email
                    </button>
                @endif

                @if (! $user->pending_email)
                    <form
                        id="email-change-form"
                        action="{{ route('profile.email.request') }}"
                        method="POST"
                        @class([
                            'mt-4 space-y-4',
                            'hidden' => ! $emailFormOpen,
                        ])
                        data-loading-form
                    >
                        @csrf
                        <div>
                            <label for="new-email" class="mb-1.5 block text-sm font-medium text-gray-700">Email mới</label>
                            <input
                                id="new-email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                maxlength="255"
                                autocomplete="email"
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            >
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current-password" class="mb-1.5 block text-sm font-medium text-gray-700">Mật khẩu hiện tại</label>
                            <div class="flex overflow-hidden rounded-xl border border-gray-300 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-100">
                                <input
                                    id="current-password"
                                    type="password"
                                    name="current_password"
                                    required
                                    autocomplete="current-password"
                                    class="min-w-0 flex-1 border-0 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-0"
                                >
                                <button
                                    type="button"
                                    class="px-3 text-sm font-medium text-emerald-700 hover:bg-emerald-50"
                                    data-toggle-password="current-password"
                                    aria-label="Hiện mật khẩu"
                                    aria-pressed="false"
                                >
                                    Hiện
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <p class="text-sm leading-6 text-gray-600">
                            Email hiện tại vẫn được sử dụng cho đến khi xác thực email mới thành công.
                        </p>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button
                                type="submit"
                                class="min-h-10 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60"
                                data-loading-label="Đang gửi mã..."
                            >
                                Gửi mã xác thực
                            </button>
                            <button
                                id="cancel-email-change-form"
                                type="button"
                                class="min-h-10 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                            >
                                Hủy
                            </button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v10a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V10z" />
                        </svg>
                        <h2 class="text-lg font-bold text-gray-900">Địa chỉ nhận hàng</h2>
                    </div>
                    <button
                        type="button"
                        onclick="openAddressModal()"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50"
                    >
                        Thêm địa chỉ
                    </button>
                </div>

                @if ($addresses->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($addresses as $address)
                            <article class="flex flex-col gap-3 rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $address->recipient_name }}</span>
                                        <span class="text-sm text-gray-600">{{ $address->phone }}</span>
                                        @if ($address->is_default)
                                            <span class="rounded-full bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white">Mặc định</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 break-words text-sm text-gray-600">{{ $address->address_detail }}</p>
                                </div>
                                <form
                                    action="{{ route('address.destroy', $address) }}"
                                    method="POST"
                                    class="shrink-0"
                                    onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-gray-500 transition hover:text-red-600">Xóa</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="py-6 text-center text-sm text-gray-500">Bạn chưa có địa chỉ nhận hàng nào.</p>
                @endif
            </section>
        </div>
    </div>

    <div id="address-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-8">
        <div class="max-h-full w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-xl sm:p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Thêm địa chỉ nhận hàng</h2>
                <button type="button" onclick="closeAddressModal()" class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100" aria-label="Đóng">×</button>
            </div>
            <form action="{{ route('address.store') }}" method="POST" class="space-y-4" data-loading-form>
                @csrf
                <div>
                    <label for="recipient-name" class="mb-1.5 block text-sm font-medium text-gray-700">Tên người nhận</label>
                    <input id="recipient-name" type="text" name="recipient_name" required maxlength="255" autocomplete="name" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('recipient_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="address-phone" class="mb-1.5 block text-sm font-medium text-gray-700">Số điện thoại</label>
                    <input id="address-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required maxlength="20" autocomplete="tel" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    @error('phone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="address-detail" class="mb-1.5 block text-sm font-medium text-gray-700">Địa chỉ chi tiết</label>
                    <textarea id="address-detail" name="address_detail" rows="3" required maxlength="1000" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('address_detail') }}</textarea>
                    @error('address_detail')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-600">
                    Đặt làm địa chỉ mặc định
                </label>
                <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeAddressModal()" class="min-h-10 rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">Hủy</button>
                    <button type="submit" class="min-h-10 rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60" data-loading-label="Đang lưu...">Thêm địa chỉ</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const emailChangeForm = document.getElementById('email-change-form');
        const showEmailChangeButton = document.getElementById('show-email-change-form');
        const cancelEmailChangeButton = document.getElementById('cancel-email-change-form');

        showEmailChangeButton?.addEventListener('click', () => {
            emailChangeForm?.classList.remove('hidden');
            showEmailChangeButton.classList.add('hidden');
            document.getElementById('new-email')?.focus();
        });

        cancelEmailChangeButton?.addEventListener('click', () => {
            emailChangeForm?.classList.add('hidden');
            showEmailChangeButton?.classList.remove('hidden');
            emailChangeForm?.reset();
        });

        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.togglePassword);
                if (!input) return;

                const reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                button.textContent = reveal ? 'Ẩn' : 'Hiện';
                button.setAttribute('aria-label', reveal ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
                button.setAttribute('aria-pressed', String(reveal));
            });
        });

        document.querySelectorAll('[data-loading-form]').forEach((form) => {
            form.addEventListener('submit', () => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (!submitButton) return;

                submitButton.disabled = true;
                submitButton.textContent = submitButton.dataset.loadingLabel || 'Đang xử lý...';
            });
        });

        const formatDuration = (seconds) => {
            const minutes = Math.floor(seconds / 60);
            const remainder = seconds % 60;
            return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`;
        };

        document.querySelectorAll('[data-seconds]').forEach((timer) => {
            let seconds = Math.max(0, Math.floor(Number(timer.dataset.seconds) || 0));
            const isResendTimer = timer.id === 'resend-countdown';

            const updateTimer = () => {
                if (seconds <= 0) {
                    timer.textContent = isResendTimer ? '00:00' : 'đã hết hạn';
                    if (isResendTimer) {
                        const resendButton = document.querySelector('form[action="{{ route('profile.email.verify.resend') }}"] button');
                        if (resendButton) resendButton.disabled = false;
                    }
                    return;
                }

                timer.textContent = formatDuration(seconds);
                seconds -= 1;
                window.setTimeout(updateTimer, 1000);
            };

            updateTimer();
        });

        function openAddressModal() {
            const modal = document.getElementById('address-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('recipient-name')?.focus();
        }

        function closeAddressModal() {
            const modal = document.getElementById('address-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        @if ($errors->has('recipient_name') || $errors->has('phone') || $errors->has('address_detail'))
            openAddressModal();
        @endif
    </script>
@endpush
