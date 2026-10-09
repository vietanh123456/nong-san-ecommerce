<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    /**
     * Kiểm tra quyền Admin.
     */
    private function checkAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403
        );
    }

    /**
     * Danh sách coupon.
     */
    public function index(): View
    {
        $this->checkAdmin();

        $coupons = Coupon::orderByDesc('id')
            ->paginate(10);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Form tạo coupon.
     */
    public function create(): View
    {
        $this->checkAdmin();

        return view('admin.coupons.create');
    }

    /**
     * Lưu coupon mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code',
            ],

            'type' => [
                'required',
                'in:percent,fixed',
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
            ],

            'min_order' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (
            $validated['type'] === 'percent' &&
            $validated['value'] > 100
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'value' => 'Phần trăm giảm giá không được lớn hơn 100%.',
                ]);
        }

        Coupon::create([
            'code' => strtoupper(trim($validated['code'])),

            'type' => $validated['type'],

            'value' => $validated['value'],

            'min_order' => $validated['min_order'] ?? 0,

            'max_discount' => $validated['max_discount'] ?? null,

            'usage_limit' => $validated['usage_limit'] ?? null,

            'used_count' => 0,

            'start_date' => $validated['start_date'] ?? null,

            'end_date' => $validated['end_date'] ?? null,

            'status' => $request->boolean('status'),
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Tạo mã giảm giá thành công.');
    }

    /**
     * Form sửa coupon.
     */
    public function edit(Coupon $coupon): View
    {
        $this->checkAdmin();

        return view(
            'admin.coupons.edit',
            compact('coupon')
        );
    }

    /**
     * Cập nhật coupon.
     */
    public function update(
        Request $request,
        Coupon $coupon
    ): RedirectResponse {
        $this->checkAdmin();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:coupons,code,' . $coupon->id,
            ],

            'type' => [
                'required',
                'in:percent,fixed',
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
            ],

            'min_order' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (
            $validated['type'] === 'percent' &&
            $validated['value'] > 100
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'value' => 'Phần trăm giảm giá không được lớn hơn 100%.',
                ]);
        }

        $coupon->update([
            'code' => strtoupper(trim($validated['code'])),

            'type' => $validated['type'],

            'value' => $validated['value'],

            'min_order' => $validated['min_order'] ?? 0,

            'max_discount' => $validated['max_discount'] ?? null,

            'usage_limit' => $validated['usage_limit'] ?? null,

            'start_date' => $validated['start_date'] ?? null,

            'end_date' => $validated['end_date'] ?? null,

            'status' => $request->boolean('status'),
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Cập nhật mã giảm giá thành công.');
    }

    /**
     * Xóa coupon.
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->checkAdmin();

        if ($coupon->used_count > 0) {
            return back()->withErrors([
                'coupon' =>
                    'Không thể xóa mã giảm giá đã được sử dụng. Bạn có thể tắt trạng thái của mã.',
            ]);
        }

        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Xóa mã giảm giá thành công.');
    }
}