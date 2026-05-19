@extends('layouts.app')

@section('title', 'Profil Desa')

@section('sidebar')
    <div class="nav-section">Menu Utama</div>
    <a href="{{ route('desa.dashboard') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="nav-section">Pengisian</div>
    <a href="#" class="nav-link"><i class="bi bi-pencil-square"></i> Isi Kuesioner</a>
    <a href="{{ route('desa.profil.edit') }}" class="nav-link active">
        <i class="bi bi-house-door"></i> Profil Desa
    </a>

    <div class="nav-section">Hasil</div>
    <a href="#" class="nav-link"><i class="bi bi-clipboard-data"></i> Hasil Penilaian</a>
    <a href="#" class="nav-link"><i class="bi bi-printer"></i> Cetak Laporan</a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Profil Desa</h1>
        <p class="text-secondary mb-0 small">Perbarui data identitas {{ $desa->nama }}.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('desa.profil.update') }}" novalidate>
                @csrf
                @method('PUT')
                @include('admin.desa.partials.form', ['mode' => 'edit'])
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@endsection
