<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Hiển thị form Đăng nhập
    public function showLogin()
    {
        return view('login');
    }

    // Xử lý Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

// Hiển thị form Đăng ký
public function showRegister()
{
    return view('register'); // Bỏ tiền tố "auth." đi
}
    // Xử lý Đăng ký
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('login')->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
    }

    // Hiển thị Trang cá nhân (Profile)
    public function profile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    // Cập nhật Hồ sơ cá nhân
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    // Xử lý Đăng xuất (Triệt hạ lỗi 419)
    public function logout(Request $request)
    {
        Auth::logout();

        // Xóa sạch session và tái tạo lại Token CSRF mới
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Đã đăng xuất thành công!');
    }
}