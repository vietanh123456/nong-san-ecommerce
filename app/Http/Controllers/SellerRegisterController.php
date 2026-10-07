<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SellerRegisterController extends Controller
{
    public function showForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'buyer') {
            return redirect()->route(
                $user->role === 'seller' ? 'seller.dashboard' : 'admin.dashboard'
            );
        }

        $sellerRequest = $user->latestSellerRequest;

        return view('seller.register', compact('user', 'sellerRequest'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'buyer') {
            return redirect()->route(
                $user->role === 'seller' ? 'seller.dashboard' : 'admin.dashboard'
            );
        }

        $validated = $request->validate([
            'store_name' => [
                'required',
                'string',
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
            ],
            'address' => [
                'required',
                'string',
                'max:1000',
            ],
            'description' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $created = DB::transaction(function () use ($user, $validated): bool {
            $lockedUser = $user->newQuery()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedUser->sellerRequests()->where('status', 'pending')->exists()) {
                return false;
            }

            $lockedUser->sellerRequests()->create($validated);

            return true;
        });

        if (! $created) {
            return redirect()
                ->route('seller.register')
                ->with('warning', 'Bạn đã có yêu cầu đang chờ Admin duyệt.');
        }

        return redirect()
            ->route('seller.register')
            ->with('success', 'Yêu cầu đã được gửi, vui lòng chờ Admin duyệt.');
    }
}
