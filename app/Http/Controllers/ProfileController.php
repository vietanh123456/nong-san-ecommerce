<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
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

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $addresses = $user->addresses()
            ->latest()
            ->get();

        return view('profile', compact('user', 'addresses'));
    }

    /**
     * Cập nhật thông tin tài khoản (Đổi tên)
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.Auth::id(),
            ],
        ]);

        $user = Auth::user();
        $newEmail = Str::lower(trim($validated['email']));
        $emailChanged = Str::lower($user->email) !== $newEmail;

        if ($emailChanged) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);

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

            $sendThrottleKey = $this->emailOtpSendThrottleKey($user);

            if (RateLimiter::tooManyAttempts($sendThrottleKey, 3)) {
                return back()
                    ->withInput($request->except('current_password'))
                    ->withErrors([
                        'email' => 'Bạn đã yêu cầu quá nhiều mã OTP. Vui lòng thử lại sau 5 phút.',
                    ]);
            }

            RateLimiter::hit($sendThrottleKey, self::OTP_TTL_SECONDS);
            $user->name = $validated['name'];
            $user->pending_email = $newEmail;
            $user->save();

            $this->sendEmailVerificationOtp($user, $newEmail);

            return redirect()
                ->route('profile.email.verify')
                ->with('success', 'Mã OTP xác thực đã được gửi đến email mới.');
        }

        $user->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('profile')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    public function showVerifyEmail(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->pending_email === null) {
            return redirect()
                ->route('profile')
                ->with('warning', 'Không có email mới nào đang chờ xác thực.');
        }

        return view('auth.verify-new-email', [
            'pendingEmail' => $user->pending_email,
        ]);
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

        $storedOtpHash = Cache::get($this->emailOtpCacheKey($user));

        if (! is_string($storedOtpHash) || ! Hash::check($validated['otp'], $storedOtpHash)) {
            RateLimiter::hit($attemptKey, self::OTP_TTL_SECONDS);

            return back()->withErrors([
                'otp' => 'Mã OTP không chính xác hoặc đã hết hạn.',
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

        $sendThrottleKey = $this->emailOtpSendThrottleKey($user);

        if (RateLimiter::tooManyAttempts($sendThrottleKey, 3)) {
            return back()->withErrors([
                'otp' => 'Bạn đã yêu cầu quá nhiều mã OTP. Vui lòng thử lại sau 5 phút.',
            ]);
        }

        RateLimiter::hit($sendThrottleKey, self::OTP_TTL_SECONDS);
        $this->sendEmailVerificationOtp($user, $pendingEmail);

        return back()->with('success', 'Mã OTP mới đã được gửi đến email đang chờ xác thực.');
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

    private const OTP_TTL_SECONDS = 300;

    private const MAX_OTP_ATTEMPTS = 5;

    private function sendEmailVerificationOtp(User $user, string $email): void
    {
        $code = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        Cache::put(
            $this->emailOtpCacheKey($user),
            Hash::make($code),
            now()->addSeconds(self::OTP_TTL_SECONDS)
        );

        Notification::route('mail', $email)
            ->notify(new OtpCodeNotification($code, 'email_change'));

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
