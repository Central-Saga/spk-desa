@extends('layouts.app')

@section('title', 'Dashboard Desa')

@section('sidebar')
    @include('desa.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Dashboard {{ $desa?->nama ?? 'Desa' }}</h1>
        <p class="text-secondary mb-0 small">
            @if ($periodeAktif)
                Periode aktif: <strong>{{ $periodeAktif->nama }}</strong>
                ({{ $periodeAktif->tanggal_mulai->format('d M Y') }} &mdash; {{ $periodeAktif->tanggal_selesai->format('d M Y') }})
            @else
                Belum ada periode penilaian aktif. Tunggu Super Admin mengaktifkan periode.
            @endif
        </p>
    </div>

    @unless ($desa)
        <div class="alert alert-warning border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Akun Anda belum terhubung ke desa manapun. Hubungi Super Admin untuk pengaturan.
        </div>
    @endunless

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div>
                        <div class="stat-label">Indikator Kuesioner</div>
                        <div class="stat-value">{{ $totalIndikator }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Sudah Diisi</div>
                        <div class="stat-value">{{ $sudahDijawab }} / {{ $totalIndikator }}</div>
                        <div class="progress mt-2" style="height: 6px;">
                            @php
                                $progress = $totalIndikator > 0 ? round($sudahDijawab / $totalIndikator * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $progress }}%"
                                 aria-valuenow="{{ $progress }}"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="spk-stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div>
                        <div class="stat-label">Nilai Akhir</div>
                        <div class="stat-value">
                            @if ($nilaiAkhir)
                                {{ number_format($nilaiAkhir->nilai_akhir, 2) }}
                            @else
                                <span class="text-secondary fs-5">Belum dihitung</span>
                            @endif
                        </div>
                        @if ($nilaiAkhir?->peringkat)
                            <div class="text-secondary small mt-1">
                                Peringkat #{{ $nilaiAkhir->peringkat }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center">
            <div class="flex-grow-1">
                <h2 class="h6 fw-semibold mb-1">Aksi cepat</h2>
                <p class="text-secondary small mb-0">Pengisian kuesioner periode aktif.</p>
            </div>
            @if ($periodeAktif)
                <a href="{{ route('desa.kuesioner.edit') }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Buka Form Kuesioner
                </a>
            @else
                <button class="btn btn-outline-secondary" disabled>
                    <i class="bi bi-pencil-square me-1"></i> Belum ada periode aktif
                </button>
            @endif
        </div>
    </div>
@endsection
