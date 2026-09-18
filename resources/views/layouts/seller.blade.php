<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Seller Dashboard')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 16px 32px;
            color: white;
            background: #166534;
        }

        .header a {
            color: white;
            text-decoration: none;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header-nav a:hover {
            text-decoration: underline;
        }

        .container {
            width: min(1200px, calc(100% - 32px));
            margin: 28px auto;
        }

        .card {
            padding: 24px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border: 0;
            border-radius: 6px;
            color: white;
            background: #15803d;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-warning {
            background: #d97706;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-secondary {
            background: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f9fafb;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .product-image {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            object-fit: cover;
            background: #e5e7eb;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 18px;
            border-radius: 6px;
        }

        .alert-success {
            color: #166534;
            background: #dcfce7;
        }

        .alert-error {
            color: #991b1b;
            background: #fee2e2;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions form {
            margin: 0;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 13px;
        }

        .badge-active {
            color: #166534;
            background: #dcfce7;
        }

        .badge-inactive {
            color: #991b1b;
            background: #fee2e2;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .error {
            display: block;
            margin-top: 5px;
            color: #dc2626;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px;
            }

            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .container {
                width: min(100% - 20px, 1200px);
                margin: 18px auto;
            }

            .card {
                padding: 16px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <header class="header">
        <nav class="header-nav">
            <a href="{{ route('seller.dashboard') }}">
                <strong>Seller Dashboard</strong>
            </a>

            <a href="{{ route('seller.products.index') }}">
                Sản phẩm
            </a>

            <a href="{{ route('seller.products.create') }}">
                Thêm sản phẩm
            </a>
        </nav>

        <span>
            {{ auth()->user()->name ?? 'Người bán' }}
        </span>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Dữ liệu chưa hợp lệ:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>