<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => [
                'nullable',
                Rule::in(['admin', 'seller', 'buyer']),
            ],
        ]);

        $users = User::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('sellerRequests', fn ($requests) => $requests
                            ->where('store_name', 'like', "%{$search}%"));
                });
            })
            ->when(
                $filters['role'] ?? null,
                fn ($query, string $role) => $query->where('role', $role)
            )
            ->orderByDesc('created_at')
            ->with('latestSellerRequest')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', compact('users', 'filters'));
    }

    public function demoteSeller(User $user): RedirectResponse
    {
        if ($user->role !== 'seller') {
            return redirect()
                ->route('admin.users.index')
                ->with('warning', 'Chỉ có thể chuyển tài khoản Người bán thành Người mua.');
        }

        $user->update(['role' => 'buyer']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã chuyển quyền tài khoản thành Người mua thành công!');
    }
}
