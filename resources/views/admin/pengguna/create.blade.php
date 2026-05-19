@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Tambah Pengguna</h1>
        <p class="text-secondary mb-0 small">Buat akun pengguna baru beserta role-nya.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.pengguna.store') }}" novalidate>
                @csrf

                @include('admin.pengguna.partials.form', ['mode' => 'create'])

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
