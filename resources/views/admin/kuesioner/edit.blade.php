@extends('layouts.app')

@section('title', 'Ubah Indikator')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Ubah Indikator</h1>
        <p class="text-secondary mb-0 small">
            Periode: <strong>{{ $periode->nama }}</strong>
            &middot; Sisa kuota bobot {{ number_format($sisaBobot, 2) }}
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.kuesioner.update', $kuesioner) }}" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="periode_id" value="{{ $kuesioner->periode_id }}">

                @include('admin.kuesioner.partials.form', [
                    'mode' => 'edit',
                    'kuesioner' => $kuesioner,
                    'urutanDefault' => $kuesioner->urutan,
                    'sisaBobot' => $sisaBobot,
                ])

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.kuesioner.index', ['periode' => $periode->id]) }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@endsection
