@extends('layouts.app')

@section('title', 'Hasil Penilaian' . $titleSuffix)

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Hasil Penilaian{{ $titleSuffix }}</h1>
            <p class="text-secondary mb-0 small">
                @if ($periode)
                    Periode: <strong>{{ $periode->nama }}</strong> ({{ $periode->tahun }})
                @else
                    Pilih periode untuk melihat hasil penilaian.
                @endif
            </p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('hasil.index') }}" class="row g-2 align-items-end">
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
    @elseif ($hasil->isEmpty())
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            Nilai akhir periode ini belum dihitung.
            @if (auth()->user()->isSuperAdmin())
                Buka <a href="{{ route('admin.nilai-akhir.index', ['periode' => $periode->id]) }}">menu Nilai Akhir</a>
                untuk menjalankan perhitungan.
            @endif
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h2 class="h6 fw-semibold mb-0">
                    <i class="bi bi-trophy text-warning me-2"></i>Peringkat &amp; Nilai Akhir
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 100px;">Peringkat</th>
                                <th>Desa</th>
                                <th class="text-end">Nilai Kuesioner (60%)</th>
                                <th class="text-end">Nilai Visitasi (40%)</th>
                                <th class="text-end">Nilai Akhir</th>
                                <th class="text-end pe-3" style="width: 110px;">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasil as $row)
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
                                    <td class="fw-medium">{{ $row->desa?->nama ?? 'Desa tidak tersedia' }}</td>
                                    <td class="text-end">{{ number_format($row->nilai_kuesioner, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->nilai_visitasi, 2) }}</td>
                                    <td class="text-end fw-semibold text-primary">{{ number_format($row->nilai_akhir, 2) }}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('hasil.show', $row) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
