<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Thanh toán - Nông Sản Việt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #ffffff;
        }

        h1 {
            color: #146c43;
        }

        .section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .product {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .address-item,
        .shipping-item,
        .payment-item {
            margin: 10px 0;
        }

        .payment-item {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
            color: #146c43;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .coupon-form {
            display: flex;
            gap: 10px;
        }

        .coupon-form input {
            flex: 1;
            padding: 10px;
        }

        button {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-coupon {
            background: #146c43;
            color: white;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
        }

        .btn-order {
            width: 100%;
            background: #146c43;
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 15px;
        }

        .btn-order:hover {
            background: #0f5132;
        }
        .new-address-panel { margin-bottom: 20px; }
        .new-address-panel summary { cursor: pointer; color: #146c43; font-weight: bold; }
        .new-address-fields {
            margin-top: 15px; padding: 16px; background: #f0fdf4;
            border: 1px solid #bbf7d0; border-radius: 8px;
        }
        .address-field { margin-bottom: 14px; }
        .address-field label { display: block; margin-bottom: 6px; }
        .address-field input, .address-field textarea {
            display: block; width: 100%; box-sizing: border-box; padding: 10px;
            border: 1px solid #ccc; border-radius: 5px; font: inherit;
        }
        @media (max-width: 600px) {
            body { margin: 0 auto; padding: 12px; }
            .section { padding: 16px; }
            .coupon-form { flex-wrap: wrap; }
            .coupon-form input { min-width: 0; }
        }
    </style>
</head>

<body>

<h1>Thanh toán</h1>


{{-- ====================================================== --}}
{{-- HIỂN THỊ THÔNG BÁO --}}
{{-- ====================================================== --}}

@if (session('success'))

    <div class="section success">
        {{ session('success') }}
    </div>

@endif


@if ($errors->any())

    <div class="section error">

        <strong>Có lỗi xảy ra:</strong>

        <ul>

            @foreach ($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


{{-- ====================================================== --}}
{{-- SẢN PHẨM --}}
{{-- ====================================================== --}}

<div class="section">

    <h2>Sản phẩm</h2>

    @foreach ($cart as $item)

        <div class="product">

            <strong>
                {{ $item['name'] }}
            </strong>

            <p>
                Giá:

                {{ number_format(
                    $item['price'],
                    0,
                    ',',
                    '.'
                ) }}đ
            </p>

            <p>
                Số lượng:
                {{ $item['quantity'] }}
            </p>

            <p>
                Thành tiền:

                <strong>

                    {{ number_format(
                        $item['price']
                        * $item['quantity'],
                        0,
                        ',',
                        '.'
                    ) }}đ

                </strong>

            </p>

        </div>

    @endforeach

</div>


{{-- ====================================================== --}}
{{-- COUPON --}}
{{-- ====================================================== --}}

<div class="section">

    <h2>Mã giảm giá</h2>

    @error('coupon_code')

        <p class="error">
            {{ $message }}
        </p>

    @enderror


    @if ($couponData)

        <p>
            Mã đang áp dụng:

            <strong>
                {{ $couponData['code'] }}
            </strong>
        </p>

        <p>
            Giảm:

            <strong>

                {{ number_format(
                    $discount,
                    0,
                    ',',
                    '.'
                ) }}đ

            </strong>
        </p>


        <form
            action="{{ route('checkout.coupon.remove') }}"
            method="POST"
        >

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="btn-remove"
            >
                Bỏ mã
            </button>

        </form>

    @else

        <form
            action="{{ route('checkout.coupon.apply') }}"
            method="POST"
            class="coupon-form"
        >

            @csrf

            <input
                type="text"
                name="coupon_code"
                placeholder="Nhập mã giảm giá"
                value="{{ old('coupon_code') }}"
            >

            <button
                type="submit"
                class="btn-coupon"
            >
                Áp dụng
            </button>

        </form>

    @endif

</div>


{{-- ====================================================== --}}
{{-- FORM ĐẶT HÀNG --}}
{{-- ====================================================== --}}

{{-- Form độc lập: các ô thêm địa chỉ liên kết bằng thuộc tính form. --}}
@auth
<form id="checkout-address-form" action="{{ route('address.store') }}" method="POST">
    @csrf
</form>
@endauth

<form
    action="{{ route('checkout.placeOrder') }}"
    method="POST"
    id="order-form"
>

    @csrf


    {{-- ================================================== --}}
    {{-- ĐỊA CHỈ NHẬN HÀNG --}}
    {{-- ================================================== --}}

    <div class="section">

        <h2>Địa chỉ nhận hàng</h2>

        @auth
            <details class="new-address-panel"
                @if($addresses->isEmpty() || $errors->has('recipient_name') || $errors->has('phone') || $errors->has('address_detail')) open @endif>
                <summary>+ Thêm địa chỉ nhận hàng mới</summary>

                <div class="new-address-fields">
                    <div class="address-field">
                        <label for="new-recipient">Tên người nhận</label>
                        <input id="new-recipient" form="checkout-address-form"
                            type="text" name="recipient_name" maxlength="255"
                            value="{{ old('recipient_name', auth()->user()->name) }}"
                            autocomplete="name" required>
                        @error('recipient_name')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="address-field">
                        <label for="new-phone">Số điện thoại nhận hàng</label>
                        <input id="new-phone" form="checkout-address-form"
                            type="tel" name="phone" maxlength="20"
                            value="{{ old('phone', auth()->user()->phone) }}"
                            autocomplete="tel" required>
                        @error('phone')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="address-field">
                        <label for="new-address">Địa chỉ đầy đủ</label>
                        <textarea id="new-address" form="checkout-address-form"
                            name="address_detail" rows="3" maxlength="1000"
                            autocomplete="street-address"
                            placeholder="Số nhà, đường, phường/xã, tỉnh/thành phố"
                            required>{{ old('address_detail') }}</textarea>
                        @error('address_detail')
                            <p class="error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label>
                        <input form="checkout-address-form" type="checkbox"
                            name="is_default" value="1" @checked(old('is_default'))>
                        Đặt làm địa chỉ mặc định
                    </label>

                    <div style="margin-top: 15px;">
                        <button form="checkout-address-form" type="submit" class="btn-coupon">
                            Lưu địa chỉ
                        </button>
                    </div>
                    <p style="color: #666; font-size: 14px;">
                        Lưu địa chỉ trước khi chọn vận chuyển và thanh toán.
                        Trang sẽ tải lại sau khi lưu.
                    </p>
                </div>
            </details>
        @endauth


        @guest
            <p>Bạn có thể đặt hàng trực tiếp, không cần đăng nhập.</p>
            <div class="new-address-fields">
                <div class="address-field">
                    <label for="guest-name">Họ tên người nhận</label>
                    <input id="guest-name" name="guest_name" type="text" maxlength="255"
                        value="{{ old('guest_name') }}" autocomplete="name" required>
                    @error('guest_name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="address-field">
                    <label for="guest-email">Email</label>
                    <input id="guest-email" name="guest_email" type="email" maxlength="255"
                        value="{{ old('guest_email') }}" autocomplete="email" required>
                    @error('guest_email') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="address-field">
                    <label for="guest-phone">Số điện thoại nhận hàng</label>
                    <input id="guest-phone" name="guest_phone" type="tel" maxlength="20" minlength="8"
                        value="{{ old('guest_phone') }}" autocomplete="tel" required>
                    @error('guest_phone') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div class="address-field">
                    <label for="guest-address">Địa chỉ nhận hàng đầy đủ</label>
                    <textarea id="guest-address" name="guest_address" rows="3" maxlength="1000"
                        autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, tỉnh/thành phố"
                        required>{{ old('guest_address') }}</textarea>
                    @error('guest_address') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endguest

        @auth
        @if ($addresses->count() > 0)

            @foreach ($addresses as $address)

                <div class="address-item">

                    <label>

                        <input
                            type="radio"
                            name="address_id"
                            value="{{ $address->id }}"

                            {{ old(
                                'address_id',
                                $addresses->firstWhere('is_default', true)?->id
                                    ?? $addresses->first()?->id
                            ) == $address->id
                                ? 'checked'
                                : ''
                            }}

                            required
                        >

                        <strong>
                            {{ $address->recipient_name }}
                        </strong>

                        -

                        {{ $address->phone }}

                        <br>

                        {{ $address->address_detail }}

                        @if ($address->is_default)

                            <strong>
                                (Mặc định)
                            </strong>

                        @endif

                    </label>

                </div>

            @endforeach

        @else

            <p>
                Bạn chưa có địa chỉ nhận hàng.
            </p>



        @endif
        @endauth

    </div>


    {{-- ================================================== --}}
    {{-- VÙNG VẬN CHUYỂN --}}
    {{-- ================================================== --}}

    <div class="section">

        <h2>Vùng vận chuyển</h2>

        @if ($shippingZones->count() > 0)

            @foreach ($shippingZones as $zone)

                @php

                    $shippingFee =
                        $zone->shippingFees->first();

                @endphp


                @if ($shippingFee)

                    <div class="shipping-item">

                        <label>

                            <input
                                type="radio"
                                name="shipping_zone_id"
                                value="{{ $zone->id }}"
                                data-fee="{{ $shippingFee->fee }}"
                                onchange="updateShippingFee(this)"

                                {{ old('shipping_zone_id') == $zone->id
                                    ? 'checked'
                                    : ''
                                }}

                                required
                            >

                            <strong>
                                {{ $zone->name }}
                            </strong>


                            @if ($zone->province)

                                -
                                {{ $zone->province }}

                            @endif

                            :

                            {{ number_format(
                                $shippingFee->fee,
                                0,
                                ',',
                                '.'
                            ) }}đ

                        </label>

                    </div>

                @endif

            @endforeach

        @else

            <p>
                Hiện chưa có vùng vận chuyển.
            </p>

        @endif

    </div>


    {{-- ================================================== --}}
    {{-- PHƯƠNG THỨC THANH TOÁN --}}
    {{-- ================================================== --}}

    <div class="section">

        <h2>Phương thức thanh toán</h2>


        {{-- COD --}}

        <div class="payment-item">

            <label>

                <input
                    type="radio"
                    name="payment_method"
                    value="cod"

                    {{ old(
                        'payment_method',
                        'cod'
                    ) === 'cod'
                        ? 'checked'
                        : ''
                    }}

                    onchange="updatePaymentButton()"
                    required
                >

                <strong>
                    💵 Thanh toán khi nhận hàng (COD)
                </strong>

            </label>

            <p
                style="
                    color: #666;
                    margin-left: 24px;
                "
            >
                Thanh toán bằng tiền mặt
                khi nhận hàng.
            </p>

        </div>


        {{-- VNPAY --}}

        <div class="payment-item">

            <label>

                <input
                    type="radio"
                    name="payment_method"
                    value="vnpay"

                    {{ old('payment_method') === 'vnpay'
                        ? 'checked'
                        : ''
                    }}

                    onchange="updatePaymentButton()"
                    required
                >

                <strong>
                    💳 Thanh toán qua VNPay
                </strong>

            </label>

            <p
                style="
                    color: #666;
                    margin-left: 24px;
                "
            >
                Thanh toán trực tuyến
                qua VNPay Sandbox.
            </p>

        </div>

    </div>


    {{-- ================================================== --}}
    {{-- GHI CHÚ --}}
    {{-- ================================================== --}}

    <div class="section">

        <h2>Ghi chú đơn hàng</h2>

        <textarea
            name="note"
            rows="4"

            style="
                width: 100%;
                box-sizing: border-box;
                padding: 10px;
            "

            placeholder="Ghi chú cho người bán (không bắt buộc)"
        >{{ old('note') }}</textarea>

    </div>


    {{-- ================================================== --}}
    {{-- TỔNG TIỀN --}}
    {{-- ================================================== --}}

    <div class="summary">

        <h2>Thông tin thanh toán</h2>


        <div class="summary-row">

            <span>
                Tạm tính:
            </span>

            <strong>

                {{ number_format(
                    $subtotal,
                    0,
                    ',',
                    '.'
                ) }}đ

            </strong>

        </div>


        <div class="summary-row">

            <span>
                Phí vận chuyển:
            </span>

            <strong id="shipping-fee">
                0đ
            </strong>

        </div>


        <div class="summary-row">

            <span>
                Giảm giá:
            </span>

            <strong>

                -{{ number_format(
                    $discount,
                    0,
                    ',',
                    '.'
                ) }}đ

            </strong>

        </div>


        <hr>


        <div class="summary-row total">

            <span>
                Tổng thanh toán:
            </span>

            <span id="grand-total">

                {{ number_format(
                    max(
                        0,
                        $subtotal - $discount
                    ),
                    0,
                    ',',
                    '.'
                ) }}đ

            </span>

        </div>

    </div>


    {{-- ================================================== --}}
    {{-- NÚT ĐẶT HÀNG --}}
    {{-- ================================================== --}}

    @if (
        (auth()->guest() || $addresses->count() > 0)
        &&
        $shippingZones->count() > 0
    )

        <button
            type="submit"
            class="btn-order"
            id="btn-order"
        >
            Đặt hàng COD
        </button>

    @else

        <button
            type="button"
            class="btn-order"
            disabled

            style="
                background: #999;
                cursor: not-allowed;
            "
        >
            Chưa đủ thông tin để đặt hàng
        </button>

    @endif

</form>


{{-- ====================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ====================================================== --}}

<script>

    const subtotal = Number(
        @json((float) $subtotal)
    );

    const discount = Number(
        @json((float) $discount)
    );


    /*
    |--------------------------------------------------------------------------
    | CẬP NHẬT PHÍ VẬN CHUYỂN
    |--------------------------------------------------------------------------
    */

    function updateShippingFee(element) {

        const fee = Number(
            element.dataset.fee
        );


        let total =
            subtotal
            + fee
            - discount;


        if (total < 0) {

            total = 0;

        }


        document
            .getElementById('shipping-fee')
            .innerText =
                fee.toLocaleString('vi-VN')
                + 'đ';


        document
            .getElementById('grand-total')
            .innerText =
                total.toLocaleString('vi-VN')
                + 'đ';

    }


    /*
    |--------------------------------------------------------------------------
    | THAY ĐỔI NÚT THEO PHƯƠNG THỨC THANH TOÁN
    |--------------------------------------------------------------------------
    */

    function updatePaymentButton() {

        const paymentMethod =
            document.querySelector(
                'input[name="payment_method"]:checked'
            );


        const button =
            document.getElementById(
                'btn-order'
            );


        if (
            !paymentMethod
            ||
            !button
        ) {

            return;

        }


        if (
            paymentMethod.value === 'vnpay'
        ) {

            button.innerText =
                'Thanh toán qua VNPay';

        } else {

            button.innerText =
                'Đặt hàng COD';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | KHI TRANG CHECKOUT ĐƯỢC MỞ
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |---------------------------------------------
            | Kiểm tra vùng vận chuyển đã chọn
            |---------------------------------------------
            */

            const checked =
                document.querySelector(
                    'input[name="shipping_zone_id"]:checked'
                );


            if (checked) {

                updateShippingFee(
                    checked
                );

            } else {

                /*
                |-----------------------------------------
                | Nếu chưa chọn thì chọn vùng đầu tiên
                |-----------------------------------------
                */

                const first =
                    document.querySelector(
                        'input[name="shipping_zone_id"]'
                    );


                if (first) {

                    first.checked = true;

                    updateShippingFee(
                        first
                    );

                }

            }


            /*
            |---------------------------------------------
            | Cập nhật tên nút thanh toán
            |---------------------------------------------
            */

            updatePaymentButton();

        }
    );

</script>

</body>

</html>