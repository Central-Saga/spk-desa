@extends('layouts.app')

@section('title', 'Manajemen Periode')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Periode</h1>
            <p class="text-secondary mb-0 small">Kelola periode pelaksanaan penilaian apresiasi desa.</p>
        </div>
        <a href="{{ route('admin.periode.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Periode
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.periode.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           class="form-control form-control-sm" placeholder="Cari nama periode...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" @selected($filters['status'] === $st->value)>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.periode.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Nama Periode</th>
                            <th>Tahun</th>
                            <th>Tanggal</th>
                            <th class="text-end">Indikator</th>
                            <th class="text-end">Visitasi</th>
                            <th class="text-end">Hasil</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periode as $row)
                            <tr>
                                <td class="ps-3 text-secondary small">
                                    {{ ($periode->currentPage() - 1) * $periode->perPage() + $loop->iteration }}
                                </td>
                                <td class="fw-medium">{{ $row->nama }}</td>
                                <td>{{ $row->tahun }}</td>
                                <td class="text-secondary small">
                                    {{ $row->tanggal_mulai->translatedFormat('d M Y') }}
                                    &mdash;
                                    {{ $row->tanggal_selesai->translatedFormat('d M Y') }}
                                </td>
                                <td class="text-end">{{ $row->kuesioner_count }}</td>
                                <td class="text-end">{{ $row->jadwal_visitasi_count }}</td>
                                <td class="text-end">{{ $row->nilai_akhir_count }}</td>
                                <td>
                                    @switch($row->status->value)
                                        @case('aktif')
                                            <span class="badge bg-success">Aktif</span>
                                            @break
                                        @case('selesai')
                                            <span class="badge bg-secondary">Selesai</span>
                                            @break
                                        @default
                                            <span class="badge bg-warning text-dark">Draft</span>
                                    @endswitch
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.periode.edit', $row) }}"
                                       class="btn btn-sm btn-outline-primary" title="Ubah">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.periode.destroy', $row) }}"
                                          class="d-inline-block"
                                          onsubmit="return confirm('Hapus periode {{ $row->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada periode penilaian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($periode->hasPages())
                <div class="mt-3">{{ $periode->links() }}</div>
            @endif
        </div>
    </div>
@endsection
