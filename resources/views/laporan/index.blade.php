@extends('layouts.app')

@section('title', 'Cetak Laporan')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Cetak Laporan</h1>
        <p class="text-secondary mb-0 small">Hasilkan laporan dalam format PDF untuk dokumentasi dan evaluasi.</p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label class="form-label small fw-medium">Periode Penilaian</label>
                    <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                        @forelse ($periodeOptions as $opt)
                            <option value="{{ $opt->id }}" @selected($periode?->id === $opt->id)>
                                {{ $opt->nama }} ({{ $opt->tahun }} &middot; {{ $opt->status->label() }})
                            </option>
                        @empty
                            <option value="">Belum ada periode</option>
                        @endforelse
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if (! $periode)
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>Belum ada periode tersedia.
        </div>
    @else
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                            </div>
                            <div>
                                <h2 class="h6 fw-semibold mb-0">Laporan Rekapitulasi</h2>
                                <p class="text-secondary mb-0 small">Daftar peringkat seluruh desa dalam satu periode.</p>
                            </div>
                        </div>
                        <a href="{{ route('laporan.rekapitulasi', $periode) }}"
                           target="_blank"
                           class="btn btn-primary w-100">
                            <i class="bi bi-printer me-1"></i> Cetak Rekapitulasi
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-file-earmark-person"></i>
                            </div>
                            <div>
                                <h2 class="h6 fw-semibold mb-0">Laporan Per Desa</h2>
                                <p class="text-secondary mb-0 small">Detail jawaban kuesioner & penilaian visitasi per desa.</p>
                            </div>
                        </div>

                        @php
                            $nilaiList = \App\Models\NilaiAkhir::query()
                                ->where('periode_id', $periode->id)
                                ->when(
                                    auth()->user()->isStaffAdminDesa() && auth()->user()->desa_id,
                                    fn ($q) => $q->where('desa_id', auth()->user()->desa_id)
                                )
                                ->with('desa')
                                ->orderBy('peringkat')
                                ->get();
                        @endphp

                        @if ($nilaiList->isEmpty())
                            <div class="alert alert-warning small mb-0">
                                Nilai akhir periode ini belum dihitung.
                            </div>
                        @else
                            <div class="list-group list-group-flush" style="max-height: 240px; overflow-y: auto;">
                                @foreach ($nilaiList as $row)
                                    <a href="{{ route('laporan.per-desa', $row) }}"
                                       target="_blank"
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center small">
                                        <span>
                                            #{{ $row->peringkat ?? '—' }} &middot; {{ $row->desa->nama }}
                                        </span>
                                        <span class="badge bg-primary">{{ number_format($row->nilai_akhir, 2) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($canCetakAudit)
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <h2 class="h6 fw-semibold mb-0">Laporan Audit Trail</h2>
                                    <p class="text-secondary mb-0 small">Riwayat aktivitas pengguna pada rentang tanggal tertentu.</p>
                                </div>
                            </div>

                            <form method="GET" action="{{ route('laporan.audit-trail') }}" target="_blank"
                                  class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-medium">Dari Tanggal</label>
                                    <input type="date" name="from" class="form-control form-control-sm"
                                           value="{{ now()->subDays(30)->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-medium">Sampai Tanggal</label>
                                    <input type="date" name="to" class="form-control form-control-sm"
                                           value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-printer me-1"></i> Cetak Audit Trail
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
