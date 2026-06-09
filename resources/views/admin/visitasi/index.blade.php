@extends('layouts.app')

@section('title', 'Manajemen Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Visitasi</h1>
            <p class="text-secondary mb-0 small">Kelola pertanyaan / indikator visitasi khusus per desa, dihitung per periode.</p>
        </div>
        @if ($periode)
            <div class="d-flex gap-2">
                @if ($desa)
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSalin">
                        <i class="bi bi-clipboard-plus me-1"></i> Salin dari desa lain
                    </button>
                @endif
                <a href="{{ route('admin.visitasi.create', array_filter(['periode' => $periode->id, 'desa' => $desa?->id])) }}"
                   class="btn btn-danger btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Indikator
                </a>
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.visitasi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="periode" class="form-label small fw-medium">Periode Penilaian</label>
                    <select id="periode" name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                        @forelse ($periodeOptions as $opt)
                            <option value="{{ $opt->id }}" @selected($periode?->id === $opt->id)>
                                {{ $opt->nama }} ({{ $opt->tahun }} &middot; {{ $opt->status->label() }})
                            </option>
                        @empty
                            <option value="">Belum ada periode</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="desa" class="form-label small fw-medium">Desa</label>
                    <select id="desa" name="desa" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Desa</option>
                        @foreach ($desaOptions as $opt)
                            <option value="{{ $opt->id }}" @selected($desa?->id === $opt->id)>{{ $opt->nama }}</option>
                        @endforeach
                    </select>
                </div>
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
        @if ($desa)
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 bg-primary bg-gradient text-white">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 small text-white-50">Total Bobot — {{ $desa->nama }}</p>
                                <h3 class="mb-0 fw-bold">{{ number_format($totalBobot, 2) }} <span class="fs-6 fw-normal">/ 100</span></h3>
                            </div>
                            <i class="bi bi-sliders2 fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="badge rounded-pill bg-{{ $statusBobot['kelas'] }}-subtle text-{{ $statusBobot['kelas'] }} border border-{{ $statusBobot['kelas'] }}-subtle px-3 py-2">
                                <i class="bi bi-flag me-1"></i> {{ $statusBobot['label'] }}
                            </span>
                            <div class="small text-secondary">
                                @if ($totalBobot < 100)
                                    Masih kurang <strong>{{ number_format(100 - $totalBobot, 2) }}</strong> bobot untuk melengkapi penilaian desa ini.
                                @elseif ($totalBobot > 100)
                                    Total bobot <strong>melebihi 100</strong>. Kurangi bobot indikator agar penilaian valid.
                                @else
                                    Bobot indikator desa ini sudah pas 100. Siap dipakai untuk penilaian.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="h6 fw-semibold text-secondary mb-2">
                <i class="bi bi-geo-alt me-1"></i> Indikator Visitasi untuk {{ $desa->nama }}
            </h2>
        @else
            <div class="alert alert-info border-0 shadow-sm small">
                <i class="bi bi-info-circle me-2"></i>
                Menampilkan indikator dari <strong>semua desa</strong>. Pilih satu desa untuk melihat ringkasan bobot dan mengelola indikatornya.
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">Urut</th>
                                <th>Desa</th>
                                <th>Kode</th>
                                <th>Pertanyaan / Indikator Visitasi</th>
                                <th>Deskripsi / Keterangan</th>
                                <th class="text-end">Bobot</th>
                                <th>Status</th>
                                <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visitasi as $row)
                                <tr>
                                    <td class="ps-3 text-secondary small">#{{ $row->urutan }}</td>
                                    <td>
                                        @if ($row->desa)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $row->desa->nama }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="bi bi-globe2 me-1"></i>Umum (semua desa)
                                            </span>
                                        @endif
                                    </td>
                                    <td><code class="small">{{ $row->kode }}</code></td>
                                    <td class="small" style="max-width: 300px;">{{ \Illuminate\Support\Str::limit($row->indikator_visitasi, 90) }}</td>
                                    <td class="small text-secondary" style="max-width: 250px;">{{ \Illuminate\Support\Str::limit($row->deskripsi, 80) ?: '-' }}</td>
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
                                    <td colspan="8" class="text-center text-secondary py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        @if ($desa)
                                            Belum ada indikator visitasi khusus untuk {{ $desa->nama }} pada periode ini.
                                            @if ($globalAktifCount > 0)
                                                <div class="small mt-2">
                                                    Saat penilaian, desa ini memakai <strong>{{ $globalAktifCount }} indikator umum (global)</strong> sebagai fallback.
                                                    Tambah indikator khusus atau salin dari desa lain untuk menggantinya.
                                                </div>
                                            @endif
                                        @else
                                            Belum ada indikator visitasi pada periode ini.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($desa)
            @include('admin.visitasi.partials.modal-salin')
        @endif
    @endif
@endsection
