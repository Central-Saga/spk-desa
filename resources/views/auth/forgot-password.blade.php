@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
    <h2 class="h5 fw-semibold mb-1">Lupa Password</h2>
    <p class="text-secondary small mb-4">
        Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang password.
    </p>

    @if (session('status'))
        <div class="alert alert-success small auto-dismiss" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="nama@contoh.id" autocomplete="email" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-medium">
            <i class="bi bi-envelope-arrow-up me-2"></i>Kirim Tautan Reset
        </button>
    </form>

    <p class="text-center small mt-4 mb-0">
        <a href="{{ route('login') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke halaman masuk
        </a>
    </p>
@endsection
