<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
}