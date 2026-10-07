<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(10);

        return view('admin.users', compact('users'));
    }

    public function demote(User $user)
    {
        if ($user->role !== 'seller') {
            return redirect()
                ->route('admin.users.index')
                ->with('warning', 'Tài khoản không phải là Người bán!');
        }

        $user->update(['role' => 'buyer']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã chuyển quyền tài khoản thành Người mua thành công!');
    }
}