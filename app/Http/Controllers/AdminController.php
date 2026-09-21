<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $pendingSellers = User::where('seller_request_status', 'pending')->latest()->get();
        $sellers = User::where('role', 'seller')->latest()->get();

        return view('admin.dashboard', compact('pendingSellers', 'sellers'));
    }

    public function approveSeller(User $user): RedirectResponse
    {
        abort_unless($user->seller_request_status === 'pending' && $user->role === 'customer', 422, 'Tài khoản này không ở trạng thái chờ duyệt.');
        $user->update(['role' => 'seller', 'seller_request_status' => 'approved']);

        return back()->with('success', "Đã duyệt seller {$user->name}.");
    }

    public function rejectSeller(User $user): RedirectResponse
    {
        abort_unless($user->seller_request_status === 'pending' && $user->role === 'customer', 422, 'Tài khoản này không ở trạng thái chờ duyệt.');
        $user->update(['role' => 'customer', 'seller_request_status' => 'rejected']);

        return back()->with('success', "Đã từ chối yêu cầu seller của {$user->name}.");
    }

    public function revokeSeller(User $user): RedirectResponse
    {
        abort_unless($user->role === 'seller', 422, 'Tài khoản này không phải seller.');
        $user->update(['role' => 'customer', 'seller_request_status' => null]);

        return back()->with('success', "Đã thu hồi quyền seller của {$user->name}.");
    }
}
