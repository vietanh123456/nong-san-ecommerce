<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CHECKOUT
    |--------------------------------------------------------------------------
    */

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

        $subtotal = $this->calculateSubtotal($cart);

        $addresses = collect();

        if (auth()->check()) {
            $addresses = Address::where('user_id', auth()->id())
                ->orderByDesc('is_default')
                ->get();
        }

        $shippingZones = ShippingZone::with([
            'shippingFees' => function ($query) {
                $query->where('status', true);
            }
        ])
            ->where('status', true)
            ->get();

        $couponData = session()->get('coupon');

        $discount = 0;

        if ($couponData) {
            $coupon = Coupon::find(
                $couponData['id'] ?? null
            );

            if (
                $coupon &&
                $this->couponIsValid($coupon, $subtotal)
            ) {
                $discount = $this->calculateDiscount(
                    $coupon,
                    $subtotal
                );

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

    /*
    |--------------------------------------------------------------------------
    | APPLY COUPON
    |--------------------------------------------------------------------------
    */

    public function applyCoupon(Request $request): RedirectResponse
    {
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
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'Mã giảm giá không tồn tại.',
                ]);
        }

        if (!$coupon->status) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'Mã giảm giá đã bị vô hiệu hóa.',
                ]);
        }

        $now = now();

        if (
            $coupon->start_date &&
            $now->lt($coupon->start_date)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'Mã giảm giá chưa đến thời gian sử dụng.',
                ]);
        }

        if (
            $coupon->end_date &&
            $now->gt($coupon->end_date)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'Mã giảm giá đã hết hạn.',
                ]);
        }

        if (
            $coupon->usage_limit !== null &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'Mã giảm giá đã hết lượt sử dụng.',
                ]);
        }

        if (
            $subtotal <
            (float) $coupon->min_order
        ) {
            return back()
                ->withInput()
                ->withErrors([
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

    /*
    |--------------------------------------------------------------------------
    | REMOVE COUPON
    |--------------------------------------------------------------------------
    */

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('coupon');

        return back()->with(
            'success',
            'Đã bỏ mã giảm giá.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PLACE ORDER
    |--------------------------------------------------------------------------
    */

    public function placeOrder(Request $request): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'login' =>
                        'Bạn cần đăng nhập để đặt hàng.',
                ]);
        }

        $validated = $request->validate([
            'address_id' => [
                'required',
                'integer',
                'exists:addresses,id',
            ],

            'shipping_zone_id' => [
                'required',
                'integer',
                'exists:shipping_zones,id',
            ],

            'payment_method' => [
                'required',
                'in:cod,vnpay',
            ],

            'note' => [
                'nullable',
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

        /*
        |--------------------------------------------------------------------------
        | ADDRESS
        |--------------------------------------------------------------------------
        */

        $address = Address::where(
            'id',
            $validated['address_id']
        )
            ->where(
                'user_id',
                auth()->id()
            )
            ->first();

        if (!$address) {
            return back()
                ->withInput()
                ->withErrors([
                    'address_id' =>
                        'Địa chỉ nhận hàng không hợp lệ.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SHIPPING
        |--------------------------------------------------------------------------
        */

        $shippingZone = ShippingZone::with([
            'shippingFees' => function ($query) {
                $query->where('status', true);
            }
        ])
            ->where(
                'id',
                $validated['shipping_zone_id']
            )
            ->where('status', true)
            ->first();

        if (!$shippingZone) {
            return back()
                ->withInput()
                ->withErrors([
                    'shipping_zone_id' =>
                        'Vùng vận chuyển không hợp lệ.',
                ]);
        }

        $shippingFeeModel =
            $shippingZone->shippingFees->first();

        if (!$shippingFeeModel) {
            return back()
                ->withInput()
                ->withErrors([
                    'shipping_zone_id' =>
                        'Vùng vận chuyển chưa có phí.',
                ]);
        }

        $shippingFee =
            (float) $shippingFeeModel->fee;

        /*
        |--------------------------------------------------------------------------
        | TÍNH LẠI GIÁ TỪ DATABASE
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);

            if (!$product) {
                return back()->withErrors([
                    'order' =>
                        'Có sản phẩm không còn tồn tại.',
                ]);
            }

            $quantity = (int) $item['quantity'];

            if ($quantity <= 0) {
                return back()->withErrors([
                    'order' =>
                        'Số lượng sản phẩm không hợp lệ.',
                ]);
            }

            if ($product->stock < $quantity) {
                return back()->withErrors([
                    'order' =>
                        'Sản phẩm "'
                        . $product->name
                        . '" không đủ tồn kho.',
                ]);
            }

            $subtotal +=
                (float) $product->price
                * $quantity;
        }

        /*
        |--------------------------------------------------------------------------
        | COUPON
        |--------------------------------------------------------------------------
        */

        $discount = 0;
        $coupon = null;

        $couponData =
            session()->get('coupon');

        if ($couponData) {
            $coupon = Coupon::find(
                $couponData['id'] ?? null
            );

            if (
                $coupon &&
                $this->couponIsValid(
                    $coupon,
                    $subtotal
                )
            ) {
                $discount =
                    $this->calculateDiscount(
                        $coupon,
                        $subtotal
                    );
            } else {
                $coupon = null;
                session()->forget('coupon');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $total =
            $subtotal
            + $shippingFee
            - $discount;

        $total = max(0, $total);

        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER
        |--------------------------------------------------------------------------
        */

        try {
            $order = DB::transaction(
                function () use (
                    $cart,
                    $address,
                    $shippingFee,
                    $subtotal,
                    $discount,
                    $total,
                    $validated,
                    $coupon
                ) {
                    $order = new Order();

                    $order->user_id =
                        auth()->id();

                    $order->address_id =
                        $address->id;

                    /*
                    |----------------------------------------------------------
                    | Lưu coupon vào đơn hàng
                    |----------------------------------------------------------
                    */

                    $order->coupon_id =
                        $coupon?->id;

                    $order->subtotal =
                        $subtotal;

                    $order->shipping_fee =
                        $shippingFee;

                    $order->discount =
                        $discount;

                    $order->total =
                        $total;

                    $order->payment_method =
                        $validated['payment_method'];

                    $order->payment_status =
                        'unpaid';

                    $order->status =
                        'pending';

                    $order->note =
                        $validated['note'] ?? null;

                    $order->save();

                    /*
                    |--------------------------------------------------------------------------
                    | ORDER DETAILS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $cart as $productId => $item
                    ) {
                        $product =
                            Product::lockForUpdate()
                                ->find($productId);

                        if (!$product) {
                            throw new \Exception(
                                'Sản phẩm không còn tồn tại.'
                            );
                        }

                        $quantity =
                            (int) $item['quantity'];

                        if (
                            $product->stock <
                            $quantity
                        ) {
                            throw new \Exception(
                                'Sản phẩm "'
                                . $product->name
                                . '" không đủ tồn kho.'
                            );
                        }

                        $price =
                            (float) $product->price;

                        $itemSubtotal =
                            $price * $quantity;

                        $orderDetail =
                            new OrderDetail();

                        $orderDetail->order_id =
                            $order->id;

                        $orderDetail->product_id =
                            $product->id;

                        $orderDetail->quantity =
                            $quantity;

                        $orderDetail->price =
                            $price;

                        $orderDetail->subtotal =
                            $itemSubtotal;

                        $orderDetail->save();

                        /*
                        |--------------------------------------------------------------------------
                        | COD trừ kho ngay
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $validated['payment_method']
                            === 'cod'
                        ) {
                            $product->stock -=
                                $quantity;

                            $product->save();
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | COD dùng coupon ngay
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $coupon &&
                        $validated['payment_method']
                        === 'cod'
                    ) {
                        $coupon->increment(
                            'used_count'
                        );
                    }

                    return $order;
                }
            );

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'order' =>
                        'Không thể tạo đơn hàng: '
                        . $e->getMessage(),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | COD
        |--------------------------------------------------------------------------
        */

        if (
            $validated['payment_method']
            === 'cod'
        ) {
            session()->forget('cart');
            session()->forget('coupon');

            return redirect()
                ->route('products.index')
                ->with(
                    'success',
                    'Đặt hàng COD thành công!'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VNPAY
        |--------------------------------------------------------------------------
        */

        return $this->redirectToVnpay(
            $request,
            $order
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REDIRECT TO VNPAY
    |--------------------------------------------------------------------------
    */

    private function redirectToVnpay(
        Request $request,
        Order $order
    ): RedirectResponse {
        $vnpUrl =
            config('services.vnpay.url');

        $tmnCode =
            config('services.vnpay.tmn_code');

        $hashSecret =
            config('services.vnpay.hash_secret');

        $returnUrl =
            config('services.vnpay.return_url');

        if (
            !$vnpUrl ||
            !$tmnCode ||
            !$hashSecret ||
            !$returnUrl
        ) {
            return redirect()
                ->route('checkout.index')
                ->withErrors([
                    'vnpay' =>
                        'Cấu hình VNPay chưa đầy đủ.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VNPAY PARAMETERS
        |--------------------------------------------------------------------------
        */

        $inputData = [
            'vnp_Version' => '2.1.0',

            'vnp_TmnCode' =>
                $tmnCode,

            'vnp_Amount' =>
                (string) round(
                    (float) $order->total * 100
                ),

            'vnp_Command' =>
                'pay',

            'vnp_CreateDate' =>
                now('Asia/Ho_Chi_Minh')
                    ->format('YmdHis'),

            'vnp_CurrCode' =>
                'VND',

            'vnp_IpAddr' =>
                $request->ip(),

            'vnp_Locale' =>
                'vn',

            'vnp_OrderInfo' =>
                'Thanh toan don hang '
                . $order->id,

            'vnp_OrderType' =>
                'other',

            'vnp_ReturnUrl' =>
                $returnUrl,

            'vnp_TxnRef' =>
                (string) $order->id,
        ];

        ksort($inputData);

        $hashData = '';
        $query = '';

        $i = 0;

        foreach ($inputData as $key => $value) {
            if (
                $value === null ||
                $value === ''
            ) {
                continue;
            }

            if ($i > 0) {
                $hashData .= '&';
                $query .= '&';
            }

            $hashData .=
                urlencode($key)
                . '='
                . urlencode($value);

            $query .=
                urlencode($key)
                . '='
                . urlencode($value);

            $i++;
        }

        $secureHash = hash_hmac(
            'sha512',
            $hashData,
            $hashSecret
        );

        $paymentUrl =
            $vnpUrl
            . '?'
            . $query
            . '&vnp_SecureHash='
            . $secureHash;

        return redirect()->away(
            $paymentUrl
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VNPAY RETURN
    |--------------------------------------------------------------------------
    */

    public function vnpayReturn(
        Request $request
    ): RedirectResponse {
        $inputData = $request->all();

        $secureHash =
            $inputData['vnp_SecureHash'] ?? null;

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        /*
        |--------------------------------------------------------------------------
        | CHECK SIGNATURE
        |--------------------------------------------------------------------------
        */

        if (!$secureHash) {
            return redirect()
                ->route('orders.index')
                ->withErrors([
                    'vnpay' =>
                        'Không nhận được chữ ký từ VNPay.',
                ]);
        }

        $hashSecret =
            config('services.vnpay.hash_secret');

        if (!$hashSecret) {
            return redirect()
                ->route('orders.index')
                ->withErrors([
                    'vnpay' =>
                        'Cấu hình VNPay chưa đầy đủ.',
                ]);
        }

        $calculatedHash =
            $this->createVnpayHash(
                $inputData,
                $hashSecret
            );

        if (
            !hash_equals(
                strtolower($calculatedHash),
                strtolower($secureHash)
            )
        ) {
            return redirect()
                ->route('orders.index')
                ->withErrors([
                    'vnpay' =>
                        'Chữ ký VNPay không hợp lệ.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FIND ORDER
        |--------------------------------------------------------------------------
        */

        $orderId =
            $inputData['vnp_TxnRef'] ?? null;

        if (!$orderId) {
            return redirect()
                ->route('orders.index')
                ->withErrors([
                    'vnpay' =>
                        'Không tìm thấy mã đơn hàng.',
                ]);
        }

        $order = Order::where(
            'id',
            $orderId
        )
            ->where(
                'user_id',
                auth()->id()
            )
            ->first();

        if (!$order) {
            return redirect()
                ->route('orders.index')
                ->withErrors([
                    'vnpay' =>
                        'Không tìm thấy đơn hàng.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK PAYMENT METHOD
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_method !==
            'vnpay'
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $order
                )
                ->withErrors([
                    'vnpay' =>
                        'Đơn hàng không sử dụng VNPay.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK AMOUNT
        |--------------------------------------------------------------------------
        */

        $vnpAmount =
            (int) (
                $inputData['vnp_Amount']
                ?? 0
            );

        $expectedAmount =
            (int) round(
                (float) $order->total
                * 100
            );

        if (
            $vnpAmount !==
            $expectedAmount
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $order
                )
                ->withErrors([
                    'vnpay' =>
                        'Số tiền thanh toán VNPay không hợp lệ.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK VNPAY RESULT
        |--------------------------------------------------------------------------
        */

        $responseCode =
            $inputData['vnp_ResponseCode']
            ?? null;

        $transactionStatus =
            $inputData['vnp_TransactionStatus']
            ?? null;

        if (
            $responseCode !== '00' ||
            $transactionStatus !== '00'
        ) {
            return redirect()
                ->route(
                    'orders.show',
                    $order
                )
                ->withErrors([
                    'vnpay' =>
                        'Thanh toán VNPay không thành công hoặc đã bị hủy.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ALREADY PAID
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_status ===
            'paid'
        ) {
            session()->forget('cart');
            session()->forget('coupon');

            return redirect()
                ->route(
                    'orders.show',
                    $order
                )
                ->with(
                    'success',
                    'Đơn hàng đã được thanh toán trước đó.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | CONFIRM PAYMENT
        |--------------------------------------------------------------------------
        */

        try {
            $this->confirmVnpayPayment(
                $order->id
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route(
                    'orders.show',
                    $order
                )
                ->withErrors([
                    'vnpay' =>
                        'VNPay đã thanh toán nhưng hệ thống '
                        . 'không thể cập nhật đơn hàng: '
                        . $e->getMessage(),
                ]);
        }

        session()->forget('cart');
        session()->forget('coupon');

        return redirect()
            ->route(
                'orders.show',
                $order
            )
            ->with(
                'success',
                'Thanh toán VNPay thành công!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | VNPAY IPN
    |--------------------------------------------------------------------------
    */

    public function vnpayIpn(Request $request)
    {
        try {
            $inputData = $request->all();

            $secureHash =
                $inputData['vnp_SecureHash']
                ?? null;

            unset(
                $inputData['vnp_SecureHash']
            );

            unset(
                $inputData['vnp_SecureHashType']
            );

            /*
            |--------------------------------------------------------------------------
            | SIGNATURE
            |--------------------------------------------------------------------------
            */

            if (!$secureHash) {
                return response()->json([
                    'RspCode' => '97',
                    'Message' =>
                        'Invalid signature',
                ]);
            }

            $hashSecret =
                config(
                    'services.vnpay.hash_secret'
                );

            if (!$hashSecret) {
                return response()->json([
                    'RspCode' => '99',
                    'Message' =>
                        'VNPay configuration error',
                ]);
            }

            $calculatedHash =
                $this->createVnpayHash(
                    $inputData,
                    $hashSecret
                );

            if (
                !hash_equals(
                    strtolower(
                        $calculatedHash
                    ),
                    strtolower(
                        $secureHash
                    )
                )
            ) {
                return response()->json([
                    'RspCode' => '97',
                    'Message' =>
                        'Invalid signature',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ORDER
            |--------------------------------------------------------------------------
            */

            $orderId =
                $inputData['vnp_TxnRef']
                ?? null;

            $order =
                Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'RspCode' => '01',
                    'Message' =>
                        'Order not found',
                ]);
            }

            if (
                $order->payment_method !==
                'vnpay'
            ) {
                return response()->json([
                    'RspCode' => '01',
                    'Message' =>
                        'Order not found',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | AMOUNT
            |--------------------------------------------------------------------------
            */

            $vnpAmount =
                (int) (
                    $inputData['vnp_Amount']
                    ?? 0
                );

            $expectedAmount =
                (int) round(
                    (float) $order->total
                    * 100
                );

            if (
                $vnpAmount !==
                $expectedAmount
            ) {
                return response()->json([
                    'RspCode' => '04',
                    'Message' =>
                        'Invalid amount',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ALREADY CONFIRMED
            |--------------------------------------------------------------------------
            */

            if (
                $order->payment_status ===
                'paid'
            ) {
                return response()->json([
                    'RspCode' => '02',
                    'Message' =>
                        'Order already confirmed',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | PAYMENT RESULT
            |--------------------------------------------------------------------------
            */

            $responseCode =
                $inputData[
                    'vnp_ResponseCode'
                ] ?? null;

            $transactionStatus =
                $inputData[
                    'vnp_TransactionStatus'
                ] ?? null;

            /*
            | VNPay đã gửi thông báo hợp lệ nhưng
            | giao dịch không thành công.
            */

            if (
                $responseCode !== '00' ||
                $transactionStatus !== '00'
            ) {
                return response()->json([
                    'RspCode' => '00',
                    'Message' =>
                        'Confirm Success',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CONFIRM PAYMENT
            |--------------------------------------------------------------------------
            */

            $this->confirmVnpayPayment(
                $order->id
            );

            return response()->json([
                'RspCode' => '00',
                'Message' =>
                    'Confirm Success',
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'RspCode' => '99',
                'Message' =>
                    'Unknown error',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM VNPAY PAYMENT
    |--------------------------------------------------------------------------
    |
    | Hàm dùng chung cho Return và IPN.
    | Nhờ lockForUpdate + payment_status nên không bị trừ kho 2 lần.
    |
    */

    private function confirmVnpayPayment(
        int $orderId
    ): void {
        DB::transaction(
            function () use ($orderId) {
                $order =
                    Order::lockForUpdate()
                        ->findOrFail($orderId);

                /*
                |--------------------------------------------------------------------------
                | Đã xử lý trước đó
                |--------------------------------------------------------------------------
                */

                if (
                    $order->payment_status ===
                    'paid'
                ) {
                    return;
                }

                $orderDetails =
                    OrderDetail::where(
                        'order_id',
                        $order->id
                    )->get();

                /*
                |--------------------------------------------------------------------------
                | STOCK
                |--------------------------------------------------------------------------
                */

                foreach (
                    $orderDetails as $detail
                ) {
                    $product =
                        Product::lockForUpdate()
                            ->find(
                                $detail->product_id
                            );

                    if (!$product) {
                        throw new \Exception(
                            'Sản phẩm trong đơn hàng không còn tồn tại.'
                        );
                    }

                    if (
                        $product->stock <
                        $detail->quantity
                    ) {
                        throw new \Exception(
                            'Sản phẩm "'
                            . $product->name
                            . '" không đủ tồn kho.'
                        );
                    }

                    $product->stock -=
                        $detail->quantity;

                    $product->save();
                }

                /*
                |--------------------------------------------------------------------------
                | COUPON
                |--------------------------------------------------------------------------
                */

                if ($order->coupon_id) {
                    $coupon =
                        Coupon::lockForUpdate()
                            ->find(
                                $order->coupon_id
                            );

                    if ($coupon) {
                        $coupon->increment(
                            'used_count'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | PAID
                |--------------------------------------------------------------------------
                */

                $order->payment_status =
                    'paid';

                $order->save();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE VNPAY HASH
    |--------------------------------------------------------------------------
    */

    private function createVnpayHash(
        array $inputData,
        string $hashSecret
    ): string {
        ksort($inputData);

        $hashData = '';
        $i = 0;

        foreach (
            $inputData as $key => $value
        ) {
            if (
                !str_starts_with(
                    $key,
                    'vnp_'
                )
            ) {
                continue;
            }

            if (
                $value === null ||
                $value === ''
            ) {
                continue;
            }

            if ($i > 0) {
                $hashData .= '&';
            }

            $hashData .=
                urlencode($key)
                . '='
                . urlencode($value);

            $i++;
        }

        return hash_hmac(
            'sha512',
            $hashData,
            $hashSecret
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE SUBTOTAL
    |--------------------------------------------------------------------------
    */

    private function calculateSubtotal(
        array $cart
    ): float {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal +=
                (float) $item['price']
                * (int) $item['quantity'];
        }

        return $subtotal;
    }

    /*
    |--------------------------------------------------------------------------
    | COUPON VALIDATION
    |--------------------------------------------------------------------------
    */

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
            $coupon->used_count >=
            $coupon->usage_limit
        ) {
            return false;
        }

        if (
            $subtotal <
            (float) $coupon->min_order
        ) {
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE DISCOUNT
    |--------------------------------------------------------------------------
    */

    private function calculateDiscount(
        Coupon $coupon,
        float $subtotal
    ): float {
        if (
            $coupon->type === 'percent'
        ) {
            $discount =
                $subtotal
                * (
                    (float) $coupon->value
                    / 100
                );

            if (
                $coupon->max_discount !==
                null
            ) {
                $discount = min(
                    $discount,
                    (float)
                        $coupon->max_discount
                );
            }
        } else {
            $discount =
                (float) $coupon->value;
        }

        return min(
            $discount,
            $subtotal
        );
    }
}