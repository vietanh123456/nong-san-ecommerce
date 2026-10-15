<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    private const OTP_TTL_SECONDS = 300;

    private const MAX_OTP_ATTEMPTS = 5;

    private const MAX_OTP_SEND_ATTEMPTS = 3;

    public function index(): View
    {
        $user = Auth::user();

        $addresses = $user->addresses()
            ->latest()
            ->get();

        $otpData = Cache::get($this->emailOtpCacheKey($user));
        $otpExpiresAt = is_array($otpData) && is_string($otpData['expires_at'] ?? null)
            ? Carbon::parse($otpData['expires_at'])
            : null;
        $resendAvailableIn = RateLimiter::tooManyAttempts(
            $this->emailOtpSendThrottleKey($user),
            self::MAX_OTP_SEND_ATTEMPTS
        )
            ? RateLimiter::availableIn($this->emailOtpSendThrottleKey($user))
            : 0;
        $otpTtlMinutes = (int) ceil(self::OTP_TTL_SECONDS / 60);
        $otpSendLimit = self::MAX_OTP_SEND_ATTEMPTS;
        $otpSendWindowMinutes = (int) ceil(self::OTP_TTL_SECONDS / 60);

        return view('profile', compact(
            'user',
            'addresses',
            'otpExpiresAt',
            'resendAvailableIn',
            'otpTtlMinutes',
            'otpSendLimit',
            'otpSendWindowMinutes'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('profile')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    public function requestEmailChange(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
        ]);

        $user = Auth::user();
        $newEmail = Str::lower(trim($validated['email']));

        if (Str::lower($user->email) === $newEmail) {
            return back()
                ->withInput($request->except('current_password'))
                ->withErrors([
                    'email' => 'Email mới phải khác email hiện tại.',
                ]);
        }

        if (
            User::query()
                ->whereRaw('LOWER(email) = ?', [$newEmail])
                ->where('id', '!=', $user->getKey())
                ->exists()
        ) {
            return back()
                ->withInput($request->except('current_password'))
                ->withErrors([
                    'email' => 'Email này đã được sử dụng.',
                ]);
        }

        return $this->issueEmailOtp($user, $newEmail, $request);
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();
        $pendingEmail = $user->pending_email;

        if ($pendingEmail === null) {
            return redirect()
                ->route('profile')
                ->withErrors([
                    'otp' => 'Không có email mới nào đang chờ xác thực.',
                ]);
        }

        $attemptKey = $this->emailOtpVerifyThrottleKey($user);

        if (RateLimiter::tooManyAttempts($attemptKey, self::MAX_OTP_ATTEMPTS)) {
            return back()->withErrors([
                'otp' => 'Bạn đã nhập sai mã quá nhiều lần. Vui lòng gửi lại mã OTP.',
            ]);
        }

        $otpData = Cache::get($this->emailOtpCacheKey($user));
        $storedOtpHash = is_array($otpData) && is_string($otpData['otp_hash'] ?? null)
            ? $otpData['otp_hash']
            : null;

        if (
            ! is_string($storedOtpHash)
            || ! is_string($otpData['expires_at'] ?? null)
            || Carbon::parse($otpData['expires_at'])->isPast()
        ) {
            Cache::forget($this->emailOtpCacheKey($user));

            return back()->withErrors([
                'otp' => 'Mã OTP đã hết hạn hoặc không còn hiệu lực. Vui lòng gửi lại mã.',
            ]);
        }

        if (! Hash::check($validated['otp'], $storedOtpHash)) {
            RateLimiter::hit($attemptKey, self::OTP_TTL_SECONDS);
            $attemptsLeft = max(
                0,
                self::MAX_OTP_ATTEMPTS - RateLimiter::attempts($attemptKey)
            );
            $message = $attemptsLeft === 0
                ? 'Bạn đã nhập sai quá 5 lần. Mã OTP đã bị khóa; vui lòng gửi lại mã.'
                : "Mã OTP không chính xác. Bạn còn {$attemptsLeft} lần thử.";

            return back()->withErrors([
                'otp' => $message,
            ]);
        }

        if (
            User::query()
                ->whereRaw('LOWER(email) = ?', [Str::lower($pendingEmail)])
                ->where('id', '!=', $user->getKey())
                ->exists()
        ) {
            return back()->withErrors([
                'otp' => 'Email này vừa được một tài khoản khác sử dụng. Hãy cập nhật email khác trong hồ sơ.',
            ]);
        }

        DB::transaction(function () use ($user, $pendingEmail): void {
            $account = User::query()->findOrFail($user->getKey());
            $account->email = $pendingEmail;
            $account->email_verified_at = now();
            $account->pending_email = null;
            $account->save();
        });

        Cache::forget($this->emailOtpCacheKey($user));
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($this->emailOtpSendThrottleKey($user));

        return redirect()
            ->route('profile')
            ->with('success', 'Email mới đã được xác thực và cập nhật thành công.');
    }

    public function resendEmailOtp(): RedirectResponse
    {
        $user = Auth::user();
        $pendingEmail = $user->pending_email;

        if ($pendingEmail === null) {
            return redirect()
                ->route('profile')
                ->withErrors([
                    'email' => 'Không có email mới nào đang chờ xác thực.',
                ]);
        }

        return $this->issueEmailOtp($user, $pendingEmail);
    }

    public function cancelEmailChange(): RedirectResponse
    {
        $user = Auth::user();

        $user->pending_email = null;
        $user->save();

        Cache::forget($this->emailOtpCacheKey($user));
        RateLimiter::clear($this->emailOtpVerifyThrottleKey($user));
        RateLimiter::clear($this->emailOtpSendThrottleKey($user));

        return redirect()
            ->route('profile')
            ->with('success', 'Đã hủy yêu cầu đổi email.');
    }

    public function showVerifyEmail(): RedirectResponse
    {
        return redirect()->route('profile');
    }

    private function issueEmailOtp(
        User $user,
        string $email,
        ?Request $request = null
    ): RedirectResponse {
        $sendThrottleKey = $this->emailOtpSendThrottleKey($user);

        if (RateLimiter::tooManyAttempts($sendThrottleKey, self::MAX_OTP_SEND_ATTEMPTS)) {
            $waitMinutes = max(1, (int) ceil(
                RateLimiter::availableIn($sendThrottleKey) / 60
            ));

            return back()
                ->withInput($request?->except('current_password') ?? [])
                ->withErrors([
                    'email' => "Bạn đã yêu cầu quá nhiều mã OTP. Vui lòng thử lại sau {$waitMinutes} phút.",
                ]);
        }

        $previousPendingEmail = $user->pending_email;
        $user->pending_email = $email;
        $user->save();

        try {
            $this->sendEmailVerificationOtp($user, $email);
        } catch (Throwable $exception) {
            Cache::forget($this->emailOtpCacheKey($user));
            $user->pending_email = $previousPendingEmail;
            $user->save();
            report($exception);

            return back()
                ->withInput($request?->except('current_password') ?? [])
                ->withErrors([
                    'email' => 'Không thể gửi mã xác thực lúc này. Vui lòng thử lại sau.',
                ]);
        }

        RateLimiter::hit($sendThrottleKey, self::OTP_TTL_SECONDS);

        return redirect()
            ->route('profile')
            ->with('success', 'Mã xác thực đã được gửi đến email mới.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
            ],
            'address_detail' => [
                'required',
                'string',
                'max:1000',
            ],
            'is_default' => [
                'nullable',
                'boolean',
            ],
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use (
            $user,
            $validated,
            $isDefault
        ): void {
            if ($isDefault) {
                $user->addresses()->update([
                    'is_default' => false,
                ]);
            }

            $user->addresses()->create([
                'recipient_name' => $validated['recipient_name'],
                'phone' => $validated['phone'],
                'address_detail' => $validated['address_detail'],
                'is_default' => $isDefault,
            ]);
        });

        return redirect()
            ->route('profile')
            ->with('success', 'Thêm địa chỉ thành công!');
    }

    public function destroyAddress(
        Address $address
    ): RedirectResponse {
        abort_unless(
            $address->user_id === Auth::id(),
            403,
            'Bạn không có quyền xóa địa chỉ này.'
        );

        $address->delete();

        return redirect()
            ->route('profile')
            ->with('success', 'Xóa địa chỉ thành công!');
    }

    private function sendEmailVerificationOtp(User $user, string $email): void
    {
        $code = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $expiresAt = now()->addSeconds(self::OTP_TTL_SECONDS);

        Cache::put($this->emailOtpCacheKey($user), [
            'otp_hash' => Hash::make($code),
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        try {
            Notification::route('mail', $email)
                ->notify(new OtpCodeNotification($code, 'email_change'));
        } catch (Throwable $exception) {
            Cache::forget($this->emailOtpCacheKey($user));
            throw $exception;
        }

        RateLimiter::clear($this->emailOtpVerifyThrottleKey($user));
    }

    private function emailOtpCacheKey(User $user): string
    {
        return 'profile-email-otp:'.$user->getKey();
    }

    private function emailOtpSendThrottleKey(User $user): string
    {
        return 'profile-email-otp-send:'.$user->getKey();
    }

    private function emailOtpVerifyThrottleKey(User $user): string
    {
        return 'profile-email-otp-verify:'.$user->getKey();
    }
}
