@extends('layouts.app')

@section('title', 'Manajemen Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Visitasi</h1>
            <p class="text-secondary mb-0 small">Kelola indikator dan bobot penilaian visitasi per periode dan per desa.</p>
        </div>
        @if ($periode)
            <a href="{{ route('admin.visitasi.create', ['periode' => $periode->id, 'desa_id' => $filterDesaId ?? '']) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Indikator
            </a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visitasi.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="periode" class="form-label small fw-medium">Periode Penilaian</label>
                    <select id="periode" name="periode" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        @forelse ($periodeOptions as $opt)
                            <option value="{{ $opt->id }}" @selected($periode?->id === $opt->id)>
                                {{ $opt->nama }} ({{ $opt->tahun }} &middot; {{ $opt->status->label() }})
                            </option>
                        @empty
                            <option value="">Belum ada periode</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="desa_id" class="form-label small fw-medium">Filter Desa</label>
                    <select id="desa_id" name="desa_id" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        <option value="">Semua Indikator</option>
                        <option value="0" @selected($filterDesaId === 0)>Global (Semua Desa)</option>
                        @foreach ($desaOptions as $d)
                            <option value="{{ $d->id }}" @selected($filterDesaId === $d->id)>
                                {{ $d->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($periode)
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0 py-2 small d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            @if ($filterDesaId === null)
                                @php
                                    $globalBobot = (float) $visitasi->whereNull('desa_id')->sum('bobot');
                                    $belumLengkap = $globalBobot < 100 ? ['Global'] : [];
                                    $desaSet = $visitasi->whereNotNull('desa_id')->groupBy('desa_id');
                                    foreach ($desaOptions as $d) {
                                        $db = (float) $desaSet->get($d->id)?->sum('bobot') ?: 0;
                                        if ($db > 0 && $db < 100) {
                                            $belumLengkap[] = $d->nama;
                                        }
                                    }
                                @endphp
                                @if (! empty($belumLengkap))
                                    <span class="text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Belum lengkap: {{ implode(', ', $belumLengkap) }}
                                    </span>
                                @else
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Semua indikator lengkap
                                    </span>
                                @endif
                            @else
                                Total bobot: <strong class="ms-1">{{ number_format($totalBobot, 2) }} / 100</strong>
                                @if ($totalBobot < 100)
                                    <span class="text-warning ms-auto">
                                        Sisa: {{ number_format(100 - $totalBobot, 2) }}
                                    </span>
                                @elseif ($totalBobot > 100)
                                    <span class="text-danger ms-auto">
                                        Lebih: {{ number_format($totalBobot - 100, 2) }}
                                    </span>
                                @else
                                    <span class="text-success ms-auto"><i class="bi bi-check-circle me-1"></i>Lengkap</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if (! $periode)
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Belum ada periode penilaian. Buat periode terlebih dahulu pada
            <a href="{{ route('admin.periode.create') }}">menu Periode</a>.
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">Urut</th>
                                <th>Desa</th>
                                <th>Kategori</th>
                                <th>Kode</th>
                                <th>Indikator Visitasi</th>
                                <th class="text-end">Bobot</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visitasi as $row)
                                <tr>
                                    <td class="ps-3 text-secondary small">#{{ $row->urutan }}</td>
                                    <td>
                                        @if ($row->desa_id)
                                            <span class="badge bg-info-subtle text-info">{{ $row->desa?->nama ?? '-' }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Global</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $row->kategori }}</span></td>
                                    <td><code class="small">{{ $row->kode }}</code></td>
                                    <td class="small" style="max-width: 300px;">{{ \Illuminate\Support\Str::limit($row->indikator_visitasi, 80) }}</td>
                                    <td class="text-end fw-medium">{{ number_format($row->bobot, 2) }}</td>
                                    <td>
                                        @if ($row->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.visitasi.edit', $row) }}"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.visitasi.destroy', $row) }}"
                                              class="d-inline-block"
                                              onsubmit="return confirm('Hapus indikator {{ $row->kode }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada indikator pada periode ini.
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
