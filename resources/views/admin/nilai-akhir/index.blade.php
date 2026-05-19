@extends('layouts.app')

@section('title', 'Hitung Nilai Akhir')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Nilai Akhir &amp; Peringkat</h1>
            <p class="text-secondary mb-0 small">Hitung nilai akhir desa dengan bobot 60% kuesioner + 40% visitasi.</p>
        </div>
        @if ($periode)
            <form method="POST" action="{{ route('admin.nilai-akhir.hitung', $periode) }}"
                  onsubmit="return confirm('Jalankan perhitungan untuk periode {{ $periode->nama }}?\n\nPerhitungan akan menggunakan data terbaru dan menggantikan hasil sebelumnya.');">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-calculator me-1"></i> Hitung Nilai Akhir
                </button>
            </form>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai-akhir.index') }}" class="row g-2 align-items-end">
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
            <i class="bi bi-exclamation-triangle me-2"></i>
            Belum ada periode penilaian. Buat periode pada
            <a href="{{ route('admin.periode.create') }}">menu Periode</a>.
        </div>
    @else
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <h2 class="h6 fw-semibold mb-0">
                    <i class="bi bi-list-check text-primary me-2"></i>Status Kelengkapan Data
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Desa</th>
                                <th>Kuesioner</th>
                                <th>Visitasi</th>
                                <th class="pe-3">Status Kelengkapan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelengkapan as $row)
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $row['desa']->nama }}</td>
                                    <td>
                                        @if ($row['kuesioner_lengkap'])
                                            <span class="text-success small">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Lengkap ({{ $row['kuesioner_terjawab'] }}/{{ $row['total_kuesioner'] }})
                                            </span>
                                        @else
                                            <span class="text-warning small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Belum lengkap ({{ $row['kuesioner_terjawab'] }}/{{ $row['total_kuesioner'] }})
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['visitasi_lengkap'])
                                            <span class="text-success small">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Lengkap ({{ $row['visitasi_dinilai'] }}/{{ $row['total_visitasi'] }})
                                            </span>
                                        @else
                                            <span class="text-warning small">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Belum lengkap ({{ $row['visitasi_dinilai'] }}/{{ $row['total_visitasi'] }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-3">
                                        @if ($row['kuesioner_lengkap'] && $row['visitasi_lengkap'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Siap dihitung</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Akan tetap dihitung dengan data tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">
                                        Belum ada desa aktif.
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
                    <i class="bi bi-trophy text-warning me-2"></i>Hasil Perhitungan &amp; Peringkat
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 80px;">Peringkat</th>
                                <th>Desa</th>
                                <th class="text-end">Nilai Kuesioner (60%)</th>
                                <th class="text-end">Nilai Visitasi (40%)</th>
                                <th class="text-end">Nilai Akhir</th>
                                <th class="pe-3">Dihitung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasil as $row)
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
                                    <td class="fw-medium">{{ $row->desa->nama }}</td>
                                    <td class="text-end">{{ number_format($row->nilai_kuesioner, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->nilai_visitasi, 2) }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($row->nilai_akhir, 2) }}</td>
                                    <td class="pe-3 small text-secondary">{{ $row->dihitung_pada?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada hasil perhitungan. Klik tombol "Hitung Nilai Akhir" di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
