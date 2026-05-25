@extends('layouts.app')

@section('title', 'Manajemen Desa')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Desa</h1>
            <p class="text-secondary mb-0 small">Kelola data identitas desa yang menjadi objek penilaian.</p>
        </div>
        <a href="{{ route('admin.desa.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Desa
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.desa.index') }}" class="row g-2 mb-3">
                <div class="col-md-9">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           class="form-control form-control-sm"
                           placeholder="Cari nama desa, kecamatan, atau kabupaten...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <a href="{{ route('admin.desa.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Nama Desa</th>
                            <th>Kecamatan</th>
                            <th>Kabupaten</th>
                            <th>Kepala Desa</th>
                            <th class="text-end">Penduduk</th>
                            <th class="text-end">Pengguna</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desa as $row)
                            <tr>
                                <td class="ps-3 text-secondary small">
                                    {{ ($desa->currentPage() - 1) * $desa->perPage() + $loop->iteration }}
                                </td>
                                <td class="fw-medium">{{ $row->nama }}</td>
                                <td class="text-secondary small">{{ $row->kecamatan }}</td>
                                <td class="text-secondary small">{{ $row->kabupaten }}</td>
                                <td class="text-secondary small">{{ $row->kepala_desa ?? '—' }}</td>
                                <td class="text-end small">
                                    {{ $row->jumlah_penduduk ? number_format($row->jumlah_penduduk) : '—' }}
                                </td>
                                <td class="text-end">{{ $row->users_count }}</td>
                                <td>
                                    @if ($row->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i>Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.desa.edit', $row) }}"
                                       class="btn btn-sm btn-outline-primary" title="Ubah">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.desa.destroy', $row) }}"
                                          class="d-inline-block"
                                          onsubmit="return confirm('Hapus desa {{ $row->nama }}?');">
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
                                    Belum ada data desa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($desa->hasPages())
                <div class="mt-3">
                    {{ $desa->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
