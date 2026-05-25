@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Dashboard Super Admin</h1>
            <p class="text-secondary mb-0 small">Ringkasan operasional sistem penilaian desa.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Pengguna</div>
                        <div class="stat-value">{{ number_format($stats['total_pengguna']) }}</div>
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
                        <div class="stat-label">Desa Aktif</div>
                        <div class="stat-value">{{ number_format($stats['total_desa']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Periode Aktif</div>
                        <div class="stat-value">{{ number_format($stats['total_periode_aktif']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label">Visitasi Terjadwal</div>
                        <div class="stat-value">{{ number_format($stats['total_visitasi_terjadwal']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 fw-semibold mb-0">
                <i class="bi bi-trophy text-warning me-2"></i>5 Peringkat Teratas
            </h2>
            <a href="#" class="btn btn-sm btn-outline-primary">
                Lihat semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 80px;">Peringkat</th>
                            <th>Desa</th>
                            <th>Periode</th>
                            <th class="text-end">Nilai Kuesioner</th>
                            <th class="text-end">Nilai Visitasi</th>
                            <th class="text-end pe-3">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rankingTerbaru as $row)
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    @if ($row->peringkat <= 3)
                                        <span class="badge bg-warning text-dark">#{{ $row->peringkat }}</span>
                                    @else
                                        #{{ $row->peringkat }}
                                    @endif
                                </td>
                                <td>{{ $row->desa?->nama ?? 'Desa tidak tersedia' }}</td>
                                <td class="text-secondary">{{ $row->periode?->nama ?? 'Periode tidak tersedia' }}</td>
                                <td class="text-end">{{ number_format($row->nilai_kuesioner, 2) }}</td>
                                <td class="text-end">{{ number_format($row->nilai_visitasi, 2) }}</td>
                                <td class="text-end pe-3 fw-semibold text-primary">
                                    {{ number_format($row->nilai_akhir, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data peringkat. Jalankan perhitungan nilai akhir terlebih dahulu.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
