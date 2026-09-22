@extends('layouts.auth')

@section('title', 'Atur Ulang Password')

@section('content')
    <h2 class="h5 fw-semibold mb-1">Atur Ulang Password</h2>
    <p class="text-secondary small mb-4">Buat password baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email" value="{{ old('email', $email ?? '') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="nama@contoh.id" autocomplete="email" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-medium">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control" placeholder="Ulangi password baru"
                       autocomplete="new-password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-medium">
            <i class="bi bi-check-lg me-2"></i>Simpan Password Baru
        </button>
    </form>
@endsection
