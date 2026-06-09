<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') &mdash; SPK Desa</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    @php
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $roleSlug = $user->primaryRoleSlug();
        $roleLabel = $roleSlug?->label() ?? 'Pengguna';
    @endphp

    <aside class="spk-sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <i class="bi bi-bar-chart-line-fill text-primary fs-4"></i>
            <div>
                <div class="fw-semibold">SPK Desa</div>
                <small class="text-secondary" style="font-size: 0.7rem;">Komisi Informasi Bali</small>
            </div>
        </div>

        <nav class="py-3">
            @yield('sidebar')
        </nav>
    </aside>

    <div class="spk-content">
        <header class="spk-topbar sticky-top">
            <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none" data-spk-sidebar-toggle>
                <i class="bi bi-list"></i>
            </button>

            <div class="ms-auto d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="small fw-medium">{{ $user->name }}</div>
                    <div class="text-secondary" style="font-size: 0.75rem;">{{ $roleLabel }}</div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-circle border" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            style="width: 40px; height: 40px;">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">{{ $user->username }}</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="p-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
