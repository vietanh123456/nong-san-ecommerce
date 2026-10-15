<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>@yield('title', 'Nông Sản Việt')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">

    @include('layouts.navigation')

    {{-- =====================================================
        THÔNG BÁO
    ====================================================== --}}

    @php($isAdminArea = request()->routeIs('admin.*'))

    {{-- Thành công --}}
    @if (session('success'))

        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">

            <div
                role="status"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-emerald-300/15 border border-emerald-300/30 text-emerald-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('success') }}
            </div>

        </div>

    @endif

    {{-- Cảnh báo --}}
    @if (session('warning'))

        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">

            <div
                role="status"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-amber-300/15 border border-amber-300/30 text-amber-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-amber-100 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('warning') }}
            </div>

        </div>

    @endif

    {{-- Lỗi --}}
    @if (session('error'))

        <div class="{{ $isAdminArea
            ? 'bg-slate-950 px-4 pt-4'
            : 'max-w-6xl w-full mx-auto mt-5 px-4'
        }}">

            <div
                role="alert"
                class="{{ $isAdminArea
                    ? 'max-w-7xl mx-auto bg-rose-300/15 border border-rose-300/30 text-rose-100 px-4 py-3 rounded-xl shadow-sm'
                    : 'bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl'
                }}"
            >
                {{ session('error') }}
            </div>

        </div>

    @endif

    {{-- =====================================================
        NỘI DUNG TRANG
    ====================================================== --}}

    <main class="flex-grow">
        @yield('content')
    </main>

    {{-- =====================================================
        FOOTER
    ====================================================== --}}

    <footer class="bg-gray-800 text-gray-400 py-6 text-center text-xs border-t border-gray-700 mt-auto">

        <p>
            © 2026 Nông Sản Việt. Tất cả quyền được bảo lưu.
        </p>

    </footer>

    @stack('scripts')

</body>
</html>