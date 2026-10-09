<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <title>Quản lý mã giảm giá</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #198754;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #f0f0f0;
        }

        .active {
            color: green;
            font-weight: bold;
        }

        .inactive {
            color: red;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .pagination {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top">

        <h1>Quản lý mã giảm giá</h1>

        <a
            href="{{ route('admin.coupons.create') }}"
            class="btn btn-primary"
        >
            + Thêm mã giảm giá
        </a>

    </div>


    @if (session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif


    @if ($errors->any())
        <div class="error">

            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach

        </div>
    @endif


    <table>

        <thead>

        <tr>
            <th>ID</th>
            <th>Mã</th>
            <th>Loại</th>
            <th>Giá trị</th>
            <th>Đơn tối thiểu</th>
            <th>Giảm tối đa</th>
            <th>Đã dùng</th>
            <th>Giới hạn</th>
            <th>Thời gian</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>

        </thead>

        <tbody>

        @forelse ($coupons as $coupon)

            <tr>

                <td>
                    {{ $coupon->id }}
                </td>

                <td>
                    <strong>
                        {{ $coupon->code }}
                    </strong>
                </td>

                <td>

                    @if ($coupon->type === 'percent')
                        Phần trăm
                    @else
                        Số tiền
                    @endif

                </td>

                <td>

                    @if ($coupon->type === 'percent')

                        {{ number_format($coupon->value, 0, ',', '.') }}%

                    @else

                        {{ number_format($coupon->value, 0, ',', '.') }}đ

                    @endif

                </td>

                <td>

                    {{ number_format(
                        $coupon->min_order ?? 0,
                        0,
                        ',',
                        '.'
                    ) }}đ

                </td>

                <td>

                    @if ($coupon->max_discount !== null)

                        {{ number_format(
                            $coupon->max_discount,
                            0,
                            ',',
                            '.'
                        ) }}đ

                    @else

                        Không giới hạn

                    @endif

                </td>

                <td>
                    {{ $coupon->used_count }}
                </td>

                <td>

                    @if ($coupon->usage_limit !== null)

                        {{ $coupon->usage_limit }}

                    @else

                        Không giới hạn

                    @endif

                </td>

                <td>

                    @if ($coupon->start_date)
                        {{ $coupon->start_date->format('d/m/Y H:i') }}
                    @else
                        Không giới hạn
                    @endif

                    <br>

                    →

                    <br>

                    @if ($coupon->end_date)
                        {{ $coupon->end_date->format('d/m/Y H:i') }}
                    @else
                        Không giới hạn
                    @endif

                </td>

                <td>

                    @if ($coupon->status)

                        <span class="active">
                            Hoạt động
                        </span>

                    @else

                        <span class="inactive">
                            Tắt
                        </span>

                    @endif

                </td>

                <td>

                    <div class="actions">

                        <a
                            href="{{ route(
                                'admin.coupons.edit',
                                $coupon
                            ) }}"
                            class="btn btn-warning"
                        >
                            Sửa
                        </a>


                        @if ($coupon->used_count == 0)

                            <form
                                action="{{ route(
                                    'admin.coupons.destroy',
                                    $coupon
                                ) }}"
                                method="POST"
                                onsubmit="return confirm(
                                    'Bạn có chắc muốn xóa mã này?'
                                );"
                            >

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    Xóa
                                </button>

                            </form>

                        @endif

                    </div>

                </td>

            </tr>

        @empty

            <tr>

                <td colspan="11">
                    Chưa có mã giảm giá nào.
                </td>

            </tr>

        @endforelse

        </tbody>

    </table>


    <div class="pagination">
        {{ $coupons->links() }}
    </div>

</div>

</body>

</html>