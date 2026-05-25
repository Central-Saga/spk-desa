@extends('layouts.app')

@section('title', 'Jadwal Visitasi')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Jadwal Visitasi</h1>
            <p class="text-secondary mb-0 small">Kelola jadwal kunjungan lapangan ke desa.</p>
        </div>
        <a href="{{ route('penilai.jadwal-visitasi.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('penilai.jadwal-visitasi.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <select name="periode" class="form-select form-select-sm">
                        <option value="">Semua Periode</option>
                        @foreach ($periodeOptions as $opt)
                            <option value="{{ $opt->id }}" @selected((int) $filters['periode'] === $opt->id)>
                                {{ $opt->nama }} ({{ $opt->tahun }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
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
                    <a href="{{ route('penilai.jadwal-visitasi.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Desa</th>
                            <th>Periode</th>
                            <th>Petugas</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwal as $row)
                            <tr>
                                <td class="ps-3 text-secondary small">
                                    {{ ($jadwal->currentPage() - 1) * $jadwal->perPage() + $loop->iteration }}
                                </td>
                                <td class="small">{{ $row->tanggal_visitasi->translatedFormat('d M Y') }}</td>
                                <td class="small">
                                    {{ \Illuminate\Support\Str::of($row->waktu_mulai)->limit(5, '') }}
                                    @if ($row->waktu_selesai)
                                        &mdash; {{ \Illuminate\Support\Str::of($row->waktu_selesai)->limit(5, '') }}
                                    @endif
                                </td>
                                <td class="fw-medium">{{ $row->desa->nama }}</td>
                                <td class="text-secondary small">{{ $row->periode->nama }}</td>
                                <td class="text-secondary small">{{ $row->petugas->name }}</td>
                                <td class="text-secondary small" style="max-width: 200px;">
                                    {{ \Illuminate\Support\Str::limit($row->lokasi, 50) }}
                                </td>
                                <td>
                                    <span class="badge {{ $row->status->badgeClass() }}">
                                        {{ $row->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('penilai.jadwal-visitasi.edit', $row) }}"
                                       class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('penilai.jadwal-visitasi.destroy', $row) }}"
                                          class="d-inline-block"
                                          onsubmit="return confirm('Hapus jadwal visitasi {{ $row->desa->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada jadwal visitasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jadwal->hasPages())
                <div class="mt-3">{{ $jadwal->links() }}</div>
            @endif
        </div>
    </div>
@endsection
