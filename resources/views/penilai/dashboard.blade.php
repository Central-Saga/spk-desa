@extends('layouts.app')

@section('title', 'Dashboard Penilai')

@section('sidebar')
    @include('penilai.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Dashboard Penilai</h1>
            <p class="text-secondary mb-0 small">Kelola jadwal visitasi dan input hasil penilaian lapangan.</p>
        </div>
        <a href="{{ route('penilai.jadwal-visitasi.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Jadwal</div>
                        <div class="stat-value">{{ $stats['total_jadwal'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="stat-label">Terjadwal</div>
                        <div class="stat-value">{{ $stats['jadwal_terjadwal'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Selesai</div>
                        <div class="stat-value">{{ $stats['jadwal_selesai'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="stat-label">Penilaian Diinput</div>
                        <div class="stat-value">{{ $stats['total_penilaian'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h2 class="h6 fw-semibold mb-0">
                <i class="bi bi-calendar-week text-primary me-2"></i>Jadwal Saya Terbaru
            </h2>
            <a href="{{ route('penilai.jadwal-visitasi.index') }}" class="btn btn-sm btn-outline-primary">
                Lihat semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Desa</th>
                            <th>Periode</th>
                            <th>Lokasi</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwalSaya as $jadwal)
                            <tr>
                                <td class="ps-3">{{ $jadwal->tanggal_visitasi->translatedFormat('d M Y') }}</td>
                                <td>{{ $jadwal->desa->nama }}</td>
                                <td class="text-secondary">{{ $jadwal->periode->nama }}</td>
                                <td class="text-secondary">{{ $jadwal->lokasi }}</td>
                                <td class="pe-3">
                                    <span class="badge {{ $jadwal->status->badgeClass() }}">
                                        {{ $jadwal->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada jadwal visitasi yang ditugaskan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
