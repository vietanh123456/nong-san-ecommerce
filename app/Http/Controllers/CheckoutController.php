<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'cart' => 'Giỏ hàng đang trống.',
                ]);
        }

        // =========================
        // TÍNH TẠM TÍNH
        // =========================

        $subtotal = $this->calculateSubtotal($cart);

        // =========================
        // ĐỊA CHỈ
        // =========================

        $addresses = collect();

        if (auth()->check()) {
            $addresses = Address::where(
                'user_id',
                auth()->id()
            )
                ->orderByDesc('is_default')
                ->get();
        }

        // =========================
        // SHIPPING
        // =========================

        $shippingZones = ShippingZone::with([
            'shippingFees' => function ($query) {
                $query->where('status', true);
            }
        ])
            ->where('status', true)
            ->get();

        // =========================
        // COUPON
        // =========================

        $couponData = session()->get('coupon');

        $discount = 0;

        if ($couponData) {
            $coupon = Coupon::find($couponData['id']);

            if ($coupon && $this->couponIsValid($coupon, $subtotal)) {
                $discount = $this->calculateDiscount(
                    $coupon,
                    $subtotal
                );

                // Cập nhật lại session để không tin dữ liệu cũ
                session()->put('coupon', [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                ]);
            } else {
                session()->forget('coupon');
                $couponData = null;
            }
        }

        return view('checkout', compact(
            'cart',
            'subtotal',
            'addresses',
            'shippingZones',
            'couponData',
            'discount'
        ));
    }

    // ==========================================
    // ÁP DỤNG COUPON
    // ==========================================

    public function applyCoupon(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'coupon_code' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'cart' => 'Giỏ hàng đang trống.',
                ]);
        }

        $subtotal = $this->calculateSubtotal($cart);

        $code = strtoupper(
            trim($validated['coupon_code'])
        );

        $coupon = Coupon::whereRaw(
            'UPPER(code) = ?',
            [$code]
        )->first();

        if (!$coupon) {
            return back()->withErrors([
                'coupon_code' =>
                    'Mã giảm giá không tồn tại.',
            ]);
        }

        if (!$coupon->status) {
            return back()->withErrors([
                'coupon_code' =>
                    'Mã giảm giá đã bị vô hiệu hóa.',
            ]);
        }

        $now = now();

        if (
            $coupon->start_date &&
            $now->lt($coupon->start_date)
        ) {
            return back()->withErrors([
                'coupon_code' =>
                    'Mã giảm giá chưa đến thời gian sử dụng.',
            ]);
        }

        if (
            $coupon->end_date &&
            $now->gt($coupon->end_date)
        ) {
            return back()->withErrors([
                'coupon_code' =>
                    'Mã giảm giá đã hết hạn.',
            ]);
        }

        if (
            $coupon->usage_limit !== null &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return back()->withErrors([
                'coupon_code' =>
                    'Mã giảm giá đã hết lượt sử dụng.',
            ]);
        }

        if ($subtotal < (float) $coupon->min_order) {
            return back()->withErrors([
                'coupon_code' =>
                    'Đơn hàng chưa đạt giá trị tối thiểu '
                    . number_format(
                        (float) $coupon->min_order,
                        0,
                        ',',
                        '.'
                    )
                    . 'đ.',
            ]);
        }

        session()->put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
        ]);

        return back()->with(
            'success',
            'Áp dụng mã giảm giá thành công!'
        );
    }

    // ==========================================
    // XÓA COUPON
    // ==========================================

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('coupon');

        return back()->with(
            'success',
            'Đã bỏ mã giảm giá.'
        );
    }

    // ==========================================
    // TÍNH SUBTOTAL
    // ==========================================

    private function calculateSubtotal(array $cart): float
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal +=
                (float) $item['price']
                * (int) $item['quantity'];
        }

        return $subtotal;
    }

    // ==========================================
    // KIỂM TRA COUPON
    // ==========================================

    private function couponIsValid(
        Coupon $coupon,
        float $subtotal
    ): bool {
        if (!$coupon->status) {
            return false;
        }

        $now = now();

        if (
            $coupon->start_date &&
            $now->lt($coupon->start_date)
        ) {
            return false;
        }

        if (
            $coupon->end_date &&
            $now->gt($coupon->end_date)
        ) {
            return false;
        }

        if (
            $coupon->usage_limit !== null &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return false;
        }

        if ($subtotal < (float) $coupon->min_order) {
            return false;
        }

        return true;
    }

    // ==========================================
    // TÍNH TIỀN GIẢM
    // ==========================================

    private function calculateDiscount(
        Coupon $coupon,
        float $subtotal
    ): float {
        if ($coupon->type === 'percent') {
            $discount =
                $subtotal
                * ((float) $coupon->value / 100);

            if ($coupon->max_discount !== null) {
                $discount = min(
                    $discount,
                    (float) $coupon->max_discount
                );
            }
        } else {
            $discount = (float) $coupon->value;
        }

        // Không cho giảm nhiều hơn tiền hàng
        return min($discount, $subtotal);
    }
}