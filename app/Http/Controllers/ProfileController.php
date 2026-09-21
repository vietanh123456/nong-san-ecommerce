<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->latest()->get();
        return view('profile', [
            'user' => Auth::user(),
            'addresses' => $addresses,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($data);

        return back()->with('success', 'Đã cập nhật thông tin cá nhân.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        Auth::user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Đã đổi mật khẩu thành công.');
    }

    public function requestSellerRole()
    {
        $user = Auth::user();

        if ($user->role === 'customer' && ! $user->isSellerPending()) {
            $user->update(['seller_request_status' => 'pending']);

            return back()->with('success', 'Đã gửi yêu cầu seller. Vui lòng chờ admin duyệt.');
        }

        return back()->with('success', $user->isSellerPending()
            ? 'Yêu cầu seller của bạn đang chờ admin duyệt.'
            : 'Tài khoản của bạn đã có quyền seller hoặc admin.');
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_detail' => ['required', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['user_id'] = Auth::id();
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $address = Address::create($data);

        if ($address->is_default || Address::where('user_id', Auth::id())->count() === 1) {
            $this->makeDefault($address);
        }

        return back()->with('success', 'Đã thêm địa chỉ nhận hàng.');
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_detail' => ['required', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        $address->update($data);

        if ($address->is_default) {
            $this->makeDefault($address);
        } elseif (! Address::where('user_id', Auth::id())->where('is_default', true)->exists()) {
            $this->makeDefault($address);
        }

        return back()->with('success', 'Đã cập nhật địa chỉ.');
    }

    public function deleteAddress(Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = Address::where('user_id', Auth::id())->latest()->first();
            if ($next) {
                $this->makeDefault($next);
            }
        }

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefaultAddress(Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);
        $this->makeDefault($address);

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    private function makeDefault(Address $address): void
    {
        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }
}