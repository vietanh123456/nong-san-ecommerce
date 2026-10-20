<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kênh người bán') - Nông Sản Việt</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #087443;
            --primary-dark: #065f36;
            --primary-soft: #e9f8f0;
            --surface: #ffffff;
            --background: #f5f7f6;
            --border: #e3e9e6;
            --text: #17231d;
            --muted: #68756e;
            --danger: #dc2626;
            --warning: #d97706;
            --sidebar-width: 270px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--text);
            background: var(--background);
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 14px;
        }

        a {
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .seller-shell {
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 30;
            display: flex;
            width: var(--sidebar-width);
            flex-direction: column;
            overflow-y: auto;
            color: #dff7e9;
            background:
                radial-gradient(circle at 20% 5%, rgba(52, 211, 153, 0.18), transparent 28%),
                linear-gradient(180deg, #064e3b 0%, #073d30 100%);
            box-shadow: 14px 0 40px rgba(6, 78, 59, 0.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 25px 22px 20px;
            color: white;
            text-decoration: none;
        }

        .brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 21px;
        }

        .brand strong,
        .brand span {
            display: block;
        }

        .brand strong {
            font-size: 16px;
            letter-spacing: -0.2px;
        }

        .brand span {
            margin-top: 3px;
            color: #9ee8bf;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar-label {
            margin: 15px 22px 8px;
            color: #77c99e;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: grid;
            gap: 5px;
            padding: 0 14px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 46px;
            padding: 10px 13px;
            border: 1px solid transparent;
            border-radius: 12px;
            color: #ccebd9;
            font-weight: 600;
            text-decoration: none;
            transition: 0.18s ease;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.09);
        }

        .nav-link.active {
            border-color: rgba(255, 255, 255, 0.15);
            color: #064e3b;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
        }

        .nav-icon {
            display: grid;
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            place-items: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.10);
            font-size: 15px;
        }

        .nav-link.active .nav-icon {
            background: var(--primary-soft);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 20px 14px;
        }

        .shop-card {
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.08);
        }

        .shop-card strong,
        .shop-card span {
            display: block;
        }

        .shop-card strong {
            overflow: hidden;
            color: white;
            font-size: 13px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .shop-card span {
            margin-top: 4px;
            color: #9ed8b7;
            font-size: 11px;
        }

        .main-area {
            min-height: 100vh;
            margin-left: var(--sidebar-width);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            min-height: 72px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 12px 30px;
            border-bottom: 1px solid rgba(227, 233, 230, 0.9);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
        }

        .topbar-title strong,
        .topbar-title span {
            display: block;
        }

        .topbar-title strong {
            font-size: 16px;
        }

        .topbar-title span {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .home-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 13px;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #345047;
            background: white;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .home-link:hover {
            border-color: #9fd7b7;
            color: var(--primary);
        }

        .container {
            width: min(1320px, calc(100% - 48px));
            margin: 28px auto 45px;
        }

        .card,
        .stat-card {
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: 0 8px 28px rgba(15, 59, 40, 0.055);
        }

        .card {
            padding: 24px;
            border-radius: 18px;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
        }

        .page-heading h1,
        .page-heading h2,
        .card h2 {
            margin: 0;
            color: #173d2e;
            letter-spacing: -0.035em;
        }

        .page-heading h1 {
            font-size: clamp(24px, 3vw, 34px);
        }

        .page-heading h2,
        .card h2 {
            font-size: 18px;
        }

        .page-heading p {
            margin: 7px 0 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            min-height: 41px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 15px;
            border: 0;
            border-radius: 11px;
            color: white;
            background: var(--primary);
            box-shadow: 0 7px 18px rgba(8, 116, 67, 0.18);
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.18s ease;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-warning { background: var(--warning); }
        .btn-danger { background: var(--danger); }
        .btn-secondary { background: #52645c; }

        .table-wrapper {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 13px 14px;
            border-bottom: 1px solid #edf1ef;
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #577067;
            background: #f7faf8;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.045em;
            text-transform: uppercase;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        tbody tr:hover {
            background: #fbfdfc;
        }

        td a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .product-image {
            width: 68px;
            height: 68px;
            border: 1px solid var(--border);
            border-radius: 13px;
            object-fit: cover;
            background: #eef3f0;
        }

        .alert {
            padding: 14px 17px;
            margin-bottom: 18px;
            border: 1px solid transparent;
            border-radius: 13px;
            line-height: 1.55;
        }

        .alert-success {
            border-color: #a7e5c0;
            color: #126339;
            background: #eafaf1;
        }

        .alert-error {
            border-color: #fecaca;
            color: #991b1b;
            background: #fff1f2;
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
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .badge-active {
            color: #087443;
            background: #dff8e9;
        }

        .badge-inactive {
            color: #b42318;
            background: #ffebe9;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #cfd9d4;
            border-radius: 11px;
            color: var(--text);
            background: white;
            outline: none;
            transition: 0.18s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #36a76f;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #324b41;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .error {
            display: block;
            margin-top: 6px;
            color: var(--danger);
            font-size: 12px;
        }

        .mobile-brand {
            display: none;
        }

        @media (max-width: 960px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .brand,
            .sidebar-label,
            .sidebar-footer {
                display: none;
            }

            .sidebar-nav {
                display: flex;
                gap: 7px;
                overflow-x: auto;
                padding: 10px 12px;
            }

            .nav-link {
                min-width: max-content;
                min-height: 40px;
                padding: 7px 11px;
                font-size: 12px;
            }

            .nav-icon {
                width: 24px;
                height: 24px;
                font-size: 13px;
            }

            .main-area {
                margin-left: 0;
            }

            .topbar {
                min-height: 62px;
                padding: 10px 16px;
            }

            .mobile-brand {
                display: block;
            }

            .topbar-title {
                display: none;
            }

            .container {
                width: min(100% - 24px, 1320px);
                margin-top: 20px;
            }
        }

        @media (max-width: 640px) {
            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading .btn {
                width: 100%;
            }

            .card {
                padding: 17px;
                border-radius: 15px;
            }

            .home-link span {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="seller-shell">
        <aside class="sidebar">
            <a href="{{ route('seller.dashboard') }}" class="brand">
                <span class="brand-mark">🌿</span>
                <span>
                    <strong>Nông Sản Việt</strong>
                    <span>Kênh người bán</span>
                </span>
            </a>

            <p class="sidebar-label">Quản lý cửa hàng</p>

            <nav class="sidebar-nav" aria-label="Điều hướng người bán">
                <a
                    href="{{ route('seller.dashboard') }}"
                    class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}"
                >
                    <span class="nav-icon">⌂</span>
                    <span>Tổng quan</span>
                </a>

                <a
                    href="{{ route('seller.products.index') }}"
                    class="nav-link {{ request()->routeIs('seller.products.index', 'seller.products.show', 'seller.products.edit') ? 'active' : '' }}"
                >
                    <span class="nav-icon">▦</span>
                    <span>Sản phẩm</span>
                </a>

                <a
                    href="{{ route('seller.products.create') }}"
                    class="nav-link {{ request()->routeIs('seller.products.create') ? 'active' : '' }}"
                >
                    <span class="nav-icon">＋</span>
                    <span>Thêm sản phẩm</span>
                </a>

                <a
                    href="{{ route('seller.orders.index') }}"
                    class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">🧾</span>
                    <span>Đơn hàng</span>
                </a>

                <a
                    href="{{ route('seller.batches.index') }}"
                    class="nav-link {{ request()->routeIs('seller.batches.*', 'seller.certificates.*') ? 'active' : '' }}"
                >
                    <span class="nav-icon">⌗</span>
                    <span>Lô hàng &amp; QR</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="shop-card">
                    <strong>{{ auth()->user()->name ?? 'Người bán' }}</strong>
                    <span>Tài khoản người bán đang hoạt động</span>
                </div>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <div class="mobile-brand">
                    <strong>🌿 Kênh người bán</strong>
                </div>

                <div class="topbar-title">
                    <strong>@yield('title', 'Kênh người bán')</strong>
                    <span>Quản lý cửa hàng và theo dõi hoạt động kinh doanh</span>
                </div>

                <div class="topbar-actions">
                    <a href="{{ route('home') }}" class="home-link">
                        <span aria-hidden="true">←</span>
                        <span>Về trang chủ</span>
                    </a>
                </div>
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
        </div>
    </div>

    @stack('scripts')
</body>
</html>
