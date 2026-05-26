@extends('layouts.app')

@section('title', 'Detail Hasil Penilaian - ' . $nilai->desa->nama)

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Detail Hasil &mdash; {{ $nilai->desa?->nama ?? 'Desa tidak tersedia' }}</h1>
            <p class="text-secondary mb-0 small">
                Periode: <strong>{{ $nilai->periode->nama }}</strong>
                &middot; Dihitung {{ $nilai->dihitung_pada?->translatedFormat('d M Y H:i') ?? '—' }}
            </p>
        </div>
        <a href="{{ route('hasil.index', ['periode' => $nilai->periode_id]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="spk-stat-card">
                <div class="stat-label">Peringkat</div>
                <div class="stat-value">
                    @if ($nilai->peringkat == 1)
                        <i class="bi bi-trophy-fill text-warning me-1"></i>
                    @endif
                    #{{ $nilai->peringkat ?? '—' }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="spk-stat-card">
                <div class="stat-label">Nilai Kuesioner (60%)</div>
                <div class="stat-value text-primary">{{ number_format($nilai->nilai_kuesioner, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="spk-stat-card">
                <div class="stat-label">Nilai Visitasi (40%)</div>
                <div class="stat-value text-info">{{ number_format($nilai->nilai_visitasi, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="spk-stat-card">
                <div class="stat-label">Nilai Akhir</div>
                <div class="stat-value text-success">{{ number_format($nilai->nilai_akhir, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-bottom py-3">
            <h2 class="h6 fw-semibold mb-0">
                <i class="bi bi-list-check text-primary me-2"></i>Detail Jawaban Kuesioner
            </h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Jawaban</th>
                            <th>Indikator</th>
                            <th>Pertanyaan</th>
                            <th>Status</th>
                            <th class="text-end">Bobot</th>
                            <th class="text-end pe-3">Skor × Bobot / 100</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jawaban as $j)
                            @php
                                $bobot = (float) ($j->kuesioner->bobot_indikator ?? 0);
                                $kontribusi = ((float) $j->skor * $bobot) / 100;
                            @endphp
                            <tr>
                                <td class="ps-3 small" style="max-width: 240px;">
                                    {{ \Illuminate\Support\Str::limit($j->jawaban, 70) ?: '—' }}
                                </td>
                                <td><code class="small">{{ $j->kuesioner?->kode_indikator ?? '—' }}</code></td>
                                <td class="small" style="max-width: 360px;">
                                    {{ \Illuminate\Support\Str::limit($j->kuesioner?->pertanyaan ?? '', 90) }}
                                </td>
                                <td>
                                    @if ($j->status_jawaban === 'iya')
                                        <span class="badge bg-success-subtle text-success">Iya</span>
                                    @elseif ($j->status_jawaban === 'tidak')
                                        <span class="badge bg-danger-subtle text-danger">Tidak</span>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-secondary">{{ number_format($bobot, 2) }}</td>
                                <td class="text-end pe-3 fw-medium text-primary">{{ number_format($kontribusi, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    Belum ada jawaban kuesioner.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h2 class="h6 fw-semibold mb-0">
                <i class="bi bi-clipboard-check text-info me-2"></i>Detail Penilaian Visitasi
            </h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Indikator Visitasi</th>
                            <th>Keterangan</th>
                            <th class="text-end">Skor</th>
                            <th class="text-end">Bobot</th>
                            <th class="text-end pe-3">Skor × Bobot / 100</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitasi as $v)
                            @php
                                $kontribusi = ((float) $v->skor * (float) $v->bobot) / 100;
                            @endphp
                            <tr>
                                <td class="ps-3 fw-medium">{{ $v->indikator_visitasi }}</td>
                                <td class="text-secondary small">{{ $v->keterangan ?: '—' }}</td>
                                <td class="text-end">{{ number_format($v->skor, 2) }}</td>
                                <td class="text-end text-secondary">{{ number_format($v->bobot, 2) }}</td>
                                <td class="text-end pe-3 fw-medium text-info">{{ number_format($kontribusi, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Belum ada penilaian visitasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
