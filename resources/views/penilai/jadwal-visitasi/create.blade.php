@extends('layouts.app')

@section('title', 'Tambah Jadwal Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Tambah Jadwal Visitasi</h1>
        <p class="text-secondary mb-0 small">Atur jadwal kunjungan lapangan ke desa.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('penilai.jadwal-visitasi.store') }}" novalidate>
                @csrf
                @include('penilai.jadwal-visitasi.partials.form', ['mode' => 'create'])
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('penilai.jadwal-visitasi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
