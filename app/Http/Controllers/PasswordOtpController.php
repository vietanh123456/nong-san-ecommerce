<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordOtpController extends Controller
{
    private const OTP_TTL_SECONDS = 300;

    private const MAX_OTP_ATTEMPTS = 5;

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetOtp(Request $request): RedirectResponse
    {
        $emailInput = trim((string) $request->input('email', ''));
        $validator = Validator::make([
            'email' => $emailInput,
        ], [
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('password.otp.form', ['email' => $emailInput])
                ->withInput(['email' => $emailInput])
                ->withErrors([
                    'email' => 'Email không tồn tại trong hệ thống hoặc không đúng định dạng!',
                ]);
        }

        $email = Str::lower($emailInput);
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($user === null) {
            return redirect()
                ->route('password.otp.form', ['email' => $email])
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Email không tồn tại trong hệ thống hoặc không đúng định dạng!',
                ]);
        }

        $sendThrottleKey = $this->sendThrottleKey($email, $request);

        if (RateLimiter::tooManyAttempts($sendThrottleKey, 3)) {
            return redirect()
                ->route('password.otp.form', ['email' => $email])
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Bạn đã yêu cầu quá nhiều mã OTP. Vui lòng chờ trước khi yêu cầu mã mới.',
                ]);
        }

        RateLimiter::hit($sendThrottleKey, self::OTP_TTL_SECONDS);
        $code = $this->generateOtp();
        $createdAt = Carbon::now('Asia/Ho_Chi_Minh');
        $expiresAt = $createdAt->copy()->addMinutes(5);

        Cache::put(
            $this->passwordOtpCacheKey($email),
            [
                'otp_hash' => Hash::make($code),
                'created_at' => $createdAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
            ],
            $expiresAt
        );

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($code));
        } catch (\Throwable $exception) {
            Cache::forget($this->passwordOtpCacheKey($email));
            RateLimiter::clear($sendThrottleKey);

            Log::error('Unable to send password reset OTP email.', [
                'user_id' => $user->getKey(),
                'exception' => $exception,
            ]);

            return redirect()
                ->route('password.otp.form', ['email' => $email])
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Gửi OTP qua Gmail SMTP thất bại. Vui lòng kiểm tra App Password 16 ký tự và cấu hình SMTP rồi thử lại.',
                ]);
        }

        RateLimiter::clear($this->verifyThrottleKey($email, $request));

        return redirect()
            ->route('password.otp.form', ['email' => $email])
            ->with('status', 'Mã OTP đã được gửi đến email của bạn và có hiệu lực trong 5 phút.');
    }

    public function showResetPassword(Request $request): View
    {
        $email = Str::lower(trim((string) old(
            'email',
            $request->query('email', '')
        )));
        $otpData = $email === ''
            ? null
            : Cache::get($this->passwordOtpCacheKey($email));
        $otpExpiresAt = is_array($otpData)
            && is_string($otpData['expires_at'] ?? null)
            ? Carbon::parse($otpData['expires_at'])
            : null;
        $otpExpired = $otpExpiresAt === null
            && $request->session()->has('errors')
            && $request->session()->get('errors')->has('otp');

        return view('auth.reset-password-otp', [
            'email' => $email,
            'otpExpiresAt' => $otpExpiresAt,
            'otpExpired' => $otpExpired,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                'confirmed',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'password.required' => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm cả chữ và số.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm cả chữ và số.',
            'password.regex' => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm cả chữ và số.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
        ]);

        $email = Str::lower(trim($validated['email']));
        $attemptKey = $this->verifyThrottleKey($email, $request);

        if (RateLimiter::tooManyAttempts($attemptKey, self::MAX_OTP_ATTEMPTS)) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors([
                    'otp' => 'Bạn đã nhập sai mã quá nhiều lần. Vui lòng yêu cầu mã OTP mới.',
                ]);
        }

        $otpData = Cache::get($this->passwordOtpCacheKey($email));

        if (
            ! is_array($otpData)
            || ! is_string($otpData['otp_hash'] ?? null)
            || ! is_string($otpData['expires_at'] ?? null)
            || Carbon::parse($otpData['expires_at'])->isPast()
            || ! Hash::check($validated['otp'], $otpData['otp_hash'])
        ) {
            RateLimiter::hit($attemptKey, self::OTP_TTL_SECONDS);

            return back()
                ->withInput(['email' => $email])
                ->withErrors([
                    'otp' => 'Mã OTP không chính xác hoặc đã hết hạn.',
                ]);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($user === null) {
            Cache::forget($this->passwordOtpCacheKey($email));

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Không thể xác thực tài khoản này. Vui lòng yêu cầu mã OTP mới.',
                ]);
        }

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        Cache::forget($this->passwordOtpCacheKey($email));
        RateLimiter::clear($attemptKey);

        return redirect()
            ->route('login')
            ->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function passwordOtpCacheKey(string $email): string
    {
        return 'password-reset-otp:'.hash('sha256', $email);
    }

    private function sendThrottleKey(string $email, Request $request): string
    {
        return 'password-reset-send:'.hash('sha256', $email.'|'.$request->ip());
    }

    private function verifyThrottleKey(string $email, Request $request): string
    {
        return 'password-reset-verify:'.hash('sha256', $email.'|'.$request->ip());
    }
}
