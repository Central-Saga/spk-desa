<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') &mdash; SPK Desa</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
        <div class="w-100" style="max-width: 440px;">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-3"
                     style="width: 64px; height: 64px;">
                    <i class="bi bi-bar-chart-line-fill fs-3"></i>
                </div>
                <h1 class="h4 fw-semibold mb-1">SPK Desa</h1>
                <p class="text-secondary small mb-0">Sistem Penilaian Kinerja Desa<br>Komisi Informasi Provinsi Bali</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    @yield('content')
                </div>
            </div>

            <p class="text-center text-secondary small mt-4 mb-0">
                &copy; {{ date('Y') }} Komisi Informasi Provinsi Bali. v{{ app()->version() }}
            </p>
        </div>
    </main>
</body>
</html>
