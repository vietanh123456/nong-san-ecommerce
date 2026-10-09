<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Sửa mã giảm giá</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        .info {
            background: #e7f1ff;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .checkbox {
            width: auto;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .buttons {
            margin-top: 25px;
        }

        button,
        a {
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #198754;
            color: white;
        }

        .back {
            background: #6c757d;
            color: white;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Sửa mã giảm giá</h1>


    <div class="info">

        Mã:
        <strong>
            {{ $coupon->code }}
        </strong>

        <br>

        Đã sử dụng:
        <strong>
            {{ $coupon->used_count }}
        </strong>
        lần

    </div>


    @if ($errors->any())

        <div class="error">

            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach

        </div>

    @endif


    <form
        action="{{ route(
            'admin.coupons.update',
            $coupon
        ) }}"
        method="POST"
    >

        @csrf

        @method('PUT')


        <div class="form-group">

            <label>
                Mã giảm giá
            </label>

            <input
                type="text"
                name="code"
                value="{{ old('code', $coupon->code) }}"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Loại giảm giá
            </label>

            <select name="type" required>

                <option
                    value="fixed"
                    {{ old('type', $coupon->type) === 'fixed'
                        ? 'selected'
                        : '' }}
                >
                    Giảm số tiền
                </option>

                <option
                    value="percent"
                    {{ old('type', $coupon->type) === 'percent'
                        ? 'selected'
                        : '' }}
                >
                    Giảm theo phần trăm
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>
                Giá trị giảm
            </label>

            <input
                type="number"
                name="value"
                value="{{ old('value', $coupon->value) }}"
                min="0"
                step="0.01"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Đơn hàng tối thiểu
            </label>

            <input
                type="number"
                name="min_order"
                value="{{ old(
                    'min_order',
                    $coupon->min_order
                ) }}"
                min="0"
                step="1000"
            >

        </div>


        <div class="form-group">

            <label>
                Giảm tối đa
            </label>

            <input
                type="number"
                name="max_discount"
                value="{{ old(
                    'max_discount',
                    $coupon->max_discount
                ) }}"
                min="0"
                step="1000"
            >

        </div>


        <div class="form-group">

            <label>
                Số lần sử dụng tối đa
            </label>

            <input
                type="number"
                name="usage_limit"
                value="{{ old(
                    'usage_limit',
                    $coupon->usage_limit
                ) }}"
                min="1"
            >

        </div>


        <div class="form-group">

            <label>
                Ngày bắt đầu
            </label>

            <input
                type="datetime-local"
                name="start_date"
                value="{{
                    old(
                        'start_date',
                        $coupon->start_date
                            ? $coupon->start_date
                                ->format('Y-m-d\TH:i')
                            : ''
                    )
                }}"
            >

        </div>


        <div class="form-group">

            <label>
                Ngày kết thúc
            </label>

            <input
                type="datetime-local"
                name="end_date"
                value="{{
                    old(
                        'end_date',
                        $coupon->end_date
                            ? $coupon->end_date
                                ->format('Y-m-d\TH:i')
                            : ''
                    )
                }}"
            >

        </div>


        <div class="form-group">

            <label>

                <input
                    type="checkbox"
                    class="checkbox"
                    name="status"
                    value="1"
                    {{ old(
                        'status',
                        $coupon->status
                    ) ? 'checked' : '' }}
                >

                Kích hoạt mã giảm giá

            </label>

        </div>


        <div class="buttons">

            <button type="submit">
                Cập nhật
            </button>

            <a
                href="{{ route('admin.coupons.index') }}"
                class="back"
            >
                Quay lại
            </a>

        </div>

    </form>

</div>

</body>

</html>