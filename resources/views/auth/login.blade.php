@extends('layouts.auth')

@section('title', 'Masuk Sistem')

@section('content')
    <h2 class="h5 fw-semibold mb-1">Masuk ke Sistem</h2>
    <p class="text-secondary small mb-4">Silakan masuk menggunakan akun yang telah terdaftar.</p>

    @if (session('status'))
        <div class="alert alert-success small auto-dismiss" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="username" class="form-label small fw-medium">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    class="form-control @error('username') is-invalid @enderror"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    autofocus
                    required
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label small" for="remember">
                Ingat saya
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-medium">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
    </form>
@endsection
