@extends('layouts.app')

@section('title', 'Manajemen Kuesioner')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Kuesioner</h1>
            <p class="text-secondary mb-0 small">Kelola indikator dan bobot penilaian kuesioner per periode.</p>
        </div>
        @if ($periode)
            <a href="{{ route('admin.kuesioner.create', ['periode' => $periode->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Indikator
            </a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kuesioner.index') }}" class="row g-2 align-items-end">
                <div class="col-md-8">
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
                @if ($periode)
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0 py-2 small d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            Total bobot:
                            <strong class="ms-1">{{ number_format($totalBobot, 2) }} / 100</strong>
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
                                <th>Kategori</th>
                                <th>Kode</th>
                                <th>Pertanyaan</th>
                                <th class="text-end">Bobot</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kuesioner as $row)
                                <tr>
                                    <td class="ps-3 text-secondary small">#{{ $row->urutan }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $row->kategori }}</span></td>
                                    <td><code class="small">{{ $row->kode_indikator }}</code></td>
                                    <td class="small" style="max-width: 420px;">{{ \Illuminate\Support\Str::limit($row->pertanyaan, 110) }}</td>
                                    <td class="text-end fw-medium">{{ number_format($row->bobot_indikator, 2) }}</td>
                                    <td>
                                        @if ($row->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.kuesioner.edit', $row) }}"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.kuesioner.destroy', $row) }}"
                                              class="d-inline-block"
                                              onsubmit="return confirm('Hapus indikator {{ $row->kode_indikator }}?');">
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
