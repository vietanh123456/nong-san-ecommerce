<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Thêm mã giảm giá</title>

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
            margin-top: 5px;
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

    <h1>Thêm mã giảm giá</h1>


    @if ($errors->any())

        <div class="error">

            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach

        </div>

    @endif


    <form
        action="{{ route('admin.coupons.store') }}"
        method="POST"
    >

        @csrf


        <div class="form-group">

            <label>
                Mã giảm giá
            </label>

            <input
                type="text"
                name="code"
                value="{{ old('code') }}"
                placeholder="Ví dụ: GIAM20K"
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
                    {{ old('type') === 'fixed' ? 'selected' : '' }}
                >
                    Giảm số tiền
                </option>

                <option
                    value="percent"
                    {{ old('type') === 'percent' ? 'selected' : '' }}
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
                value="{{ old('value') }}"
                min="0"
                step="0.01"
                placeholder="Ví dụ: 20000 hoặc 10"
                required
            >

            <small>
                Nếu là % thì nhập 10 = giảm 10%.
            </small>

        </div>


        <div class="form-group">

            <label>
                Đơn hàng tối thiểu
            </label>

            <input
                type="number"
                name="min_order"
                value="{{ old('min_order', 0) }}"
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
                value="{{ old('max_discount') }}"
                min="0"
                step="1000"
                placeholder="Để trống nếu không giới hạn"
            >

            <small>
                Chủ yếu dùng cho mã giảm theo %.
            </small>

        </div>


        <div class="form-group">

            <label>
                Số lần sử dụng tối đa
            </label>

            <input
                type="number"
                name="usage_limit"
                value="{{ old('usage_limit') }}"
                min="1"
                placeholder="Để trống nếu không giới hạn"
            >

        </div>


        <div class="form-group">

            <label>
                Ngày bắt đầu
            </label>

            <input
                type="datetime-local"
                name="start_date"
                value="{{ old('start_date') }}"
            >

        </div>


        <div class="form-group">

            <label>
                Ngày kết thúc
            </label>

            <input
                type="datetime-local"
                name="end_date"
                value="{{ old('end_date') }}"
            >

        </div>


        <div class="form-group">

            <label>

                <input
                    type="checkbox"
                    class="checkbox"
                    name="status"
                    value="1"
                    {{ old('status', true) ? 'checked' : '' }}
                >

                Kích hoạt mã giảm giá

            </label>

        </div>


        <div class="buttons">

            <button type="submit">
                Lưu mã giảm giá
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