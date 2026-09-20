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
        .shipping-item {
            margin: 10px 0;
        }

        .summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
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
    </style>
</head>

<body>

    <h1>Thanh toán</h1>

    {{-- ========================= --}}
    {{-- HIỂN THỊ LỖI --}}
    {{-- ========================= --}}

    @if ($errors->any())
        <div class="section">
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


    {{-- ========================= --}}
    {{-- SẢN PHẨM --}}
    {{-- ========================= --}}

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


    {{-- ========================= --}}
    {{-- ĐỊA CHỈ NHẬN HÀNG --}}
    {{-- ========================= --}}

    <div class="section">

        <h2>Địa chỉ nhận hàng</h2>

        @if ($addresses->count() > 0)

            @foreach ($addresses as $address)

                <div class="address-item">

                    <label>

                        <input
                            type="radio"
                            name="address_id"
                            value="{{ $address->id }}"
                            {{ $address->is_default
                                ? 'checked'
                                : '' }}
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

            @guest
                <p>
                    Hãy đăng nhập để sử dụng
                    địa chỉ đã lưu.
                </p>
            @endguest

        @endif

    </div>


    {{-- ========================= --}}
    {{-- SHIPPING ZONE --}}
    {{-- ========================= --}}

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


    {{-- ========================= --}}
    {{-- TỔNG TIỀN --}}
    {{-- ========================= --}}
    
    <div class="section">

    <h2>Mã giảm giá</h2>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    @error('coupon_code')
        <p style="color: red;">
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

            <button type="submit">
                Bỏ mã
            </button>
        </form>

    @else

        <form
            action="{{ route('checkout.coupon.apply') }}"
            method="POST"
        >
            @csrf

            <input
                type="text"
                name="coupon_code"
                placeholder="Nhập mã giảm giá"
                value="{{ old('coupon_code') }}"
            >

            <button type="submit">
                Áp dụng
            </button>

        </form>

    @endif

</div>

    <div class="summary">

    <h2>Thông tin thanh toán</h2>

    <div class="summary-row">
        <span>Tạm tính:</span>

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
        <span>Phí vận chuyển:</span>

        <strong id="shipping-fee">
            0đ
        </strong>
    </div>

    <div class="summary-row">
        <span>Giảm giá:</span>

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
                max(0, $subtotal - $discount),
                0,
                ',',
                '.'
            ) }}đ
        </span>

    </div>

</div>


    {{-- ========================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ========================= --}}

    <script>
    const subtotal = Number(
        @json((float) $subtotal)
    );

    const discount = Number(
        @json((float) $discount)
    );

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

    // Tự chọn vùng đầu tiên khi mở trang
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const checked =
                document.querySelector(
                    'input[name="shipping_zone_id"]:checked'
                );

            if (checked) {
                updateShippingFee(checked);
                return;
            }

            const first =
                document.querySelector(
                    'input[name="shipping_zone_id"]'
                );

            if (first) {
                first.checked = true;
                updateShippingFee(first);
            }
        }
    );
</script>

</body>

</html>