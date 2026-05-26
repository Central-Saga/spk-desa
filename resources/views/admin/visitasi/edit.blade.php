@extends('layouts.app')

@section('title', 'Ubah Indikator Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Ubah Indikator Visitasi</h1>
        <p class="text-secondary mb-0 small">
            Periode: <strong>{{ $periode->nama }}</strong>
            &middot; Sisa kuota bobot {{ number_format($sisaBobot, 2) }}
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.visitasi.update', $visitasi) }}" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="periode_id" value="{{ $visitasi->periode_id }}">

                @include('admin.visitasi.partials.form', [
                    'mode' => 'edit',
                    'visitasi' => $visitasi,
                    'urutanDefault' => $visitasi->urutan,
                    'sisaBobot' => $sisaBobot,
                ])

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.visitasi.index', ['periode' => $periode->id]) }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 d-flex align-items-center gap-2">
            <i class="bi bi-eye text-primary"></i>
            <span class="fw-semibold small">Pratinjau di Halaman Input Penilaian Visitasi</span>
        </div>
        <div class="card-body p-4">
            <p class="text-secondary small mb-3">Berikut tampilan indikator ini saat muncul di halaman penilaian visitasi lapangan:</p>

            <div class="row g-3 mb-3 pb-3 border-bottom">
                <div class="col-md-7">
                    <div class="d-flex gap-2 align-items-start mb-1">
                        <code class="small">{{ $visitasi->kode }}</code>
                        <span class="badge bg-secondary-subtle text-secondary">
                            Bobot {{ number_format($visitasi->bobot, 2) }}
                        </span>
                    </div>
                    <p class="mb-1 fw-medium">{{ $visitasi->indikator_visitasi }}</p>
                    @if ($visitasi->deskripsi)
                        <p class="text-secondary small mb-0">{{ $visitasi->deskripsi }}</p>
                    @endif
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium text-secondary">Skor (0-100)</label>
                    <input type="number" class="form-control form-control-sm" placeholder="..." disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium text-secondary">Keterangan</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Opsional" disabled>
                </div>
            </div>

            <div class="text-secondary small">
                <i class="bi bi-info-circle me-1"></i>
                Petugas penilaian akan mengisi skor 0-100 per indikator saat visitasi lapangan.
            </div>
        </div>
    </div>
@endsection
