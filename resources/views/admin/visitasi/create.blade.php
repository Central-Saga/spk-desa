@extends('layouts.app')

@section('title', 'Tambah Indikator Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Tambah Indikator Visitasi</h1>
        <p class="text-secondary mb-0 small">
            Periode: <strong>{{ $periode->nama }}</strong>
            @if ($desa)
                &middot; Desa: <strong>{{ $desa->nama }}</strong>
                &middot; Total bobot saat ini {{ number_format($totalBobot, 2) }} / 100
                &middot; Sisa kuota {{ number_format($sisaBobot, 2) }}
            @else
                &middot; Pilih desa pada form di bawah.
            @endif
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.visitasi.store') }}" novalidate>
                @csrf
                <input type="hidden" name="periode_id" value="{{ $periode->id }}">

                @include('admin.visitasi.partials.form', [
                    'mode' => 'create',
                    'visitasi' => null,
                    'urutanDefault' => $urutanBerikutnya,
                    'sisaBobot' => $sisaBobot,
                ])

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.visitasi.index', array_filter(['periode' => $periode->id, 'desa' => $desa?->id])) }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
