@extends('layouts.app')

@section('title', 'Dashboard Pimpinan')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Dashboard Pimpinan</h1>
        <p class="text-secondary mb-0 small">Monitoring hasil penilaian apresiasi desa.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Periode</div>
                        <div class="stat-value">{{ $stats['total_periode'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-bookmark-star"></i>
                    </div>
                    <div>
                        <div class="stat-label">Periode Aktif</div>
                        <div class="stat-value fs-6 fw-semibold">{{ $stats['periode_aktif'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Desa Dinilai</div>
                        <div class="stat-value">{{ $stats['total_desa_dinilai'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div>
                        <div class="stat-label">Rata-rata Nilai</div>
                        <div class="stat-value">{{ number_format($stats['rata_rata_nilai'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 fw-semibold mb-0">
                <i class="bi bi-trophy text-warning me-2"></i>
                Peringkat Desa &mdash; {{ $periodeAktif?->nama ?? 'Belum ada periode' }}
            </h2>
            <a href="{{ route('hasil.index') }}" class="btn btn-sm btn-outline-primary">
                Lihat detail <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 100px;">Peringkat</th>
                            <th>Desa</th>
                            <th class="text-end">Nilai Kuesioner</th>
                            <th class="text-end">Nilai Visitasi</th>
                            <th class="text-end pe-3">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranking as $row)
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    @if ($row->peringkat == 1)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> #1</span>
                                    @elseif ($row->peringkat <= 3)
                                        <span class="badge bg-info text-dark">#{{ $row->peringkat }}</span>
                                    @else
                                        #{{ $row->peringkat }}
                                    @endif
                                </td>
                                <td>{{ $row->desa->nama }}</td>
                                <td class="text-end">{{ number_format($row->nilai_kuesioner, 2) }}</td>
                                <td class="text-end">{{ number_format($row->nilai_visitasi, 2) }}</td>
                                <td class="text-end pe-3 fw-semibold text-primary">
                                    {{ number_format($row->nilai_akhir, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data peringkat untuk periode aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
