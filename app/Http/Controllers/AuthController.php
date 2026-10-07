<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        // 1. Tạo throttle key chuẩn
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 2. Kiểm tra nếu đã thử sai quá 5 lần thì chặn ngay
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return $this->loginTooManyAttemptsResponse(
                $request,
                RateLimiter::availableIn($throttleKey)
            );
        }

        // 3. Thử đăng nhập
        if (! Auth::attempt(
            $credentials + ['is_active' => true],
            $request->boolean('remember')
        )) {
            // Tăng số lần thử sai
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors([
                    'email' => 'Email hoặc mật khẩu không chính xác.',
                ])
                ->onlyInput('email');
        }

        // 4. Đăng nhập thành công -> Xóa đếm Rate Limiter & tạo lại session
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Đăng nhập thành công!');
    }

    private function loginTooManyAttemptsResponse(
        Request $request,
        int $seconds
    ): RedirectResponse {
        return back()
            ->withErrors([
                'email' => "Bạn đã đăng nhập sai quá 5 lần. Vui lòng thử lại sau {$seconds} giây.",
            ])
            ->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('register');
    }

    public function register(Request $request): RedirectResponse
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
                'unique:users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,phone',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'buyer',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Đã đăng xuất thành công!');
    }
}