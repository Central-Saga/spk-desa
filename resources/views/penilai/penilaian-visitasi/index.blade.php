@extends('layouts.app')

@section('title', 'Input Penilaian Visitasi')

@section('sidebar')
    @include('penilai.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Input Penilaian Visitasi</h1>
            <p class="text-secondary mb-0 small">Pilih jadwal untuk menginput skor penilaian lapangan.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('penilai.penilaian-visitasi.index') }}" class="row g-2 mb-3">
                <div class="col-md-9">
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
                    <a href="{{ route('penilai.penilaian-visitasi.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Desa</th>
                            <th>Periode</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th class="text-end">Indikator Dinilai</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwal as $row)
                            <tr>
                                <td class="ps-3 small">{{ $row->tanggal_visitasi->translatedFormat('d M Y') }}</td>
                                <td class="fw-medium">{{ $row->desa->nama }}</td>
                                <td class="text-secondary small">{{ $row->periode->nama }}</td>
                                <td class="text-secondary small">{{ $row->petugas->name }}</td>
                                <td>
                                    <span class="badge {{ $row->status->badgeClass() }}">{{ $row->status->label() }}</span>
                                </td>
                                <td class="text-end">{{ $row->penilaian_count }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('penilai.penilaian-visitasi.edit', $row) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-clipboard-check me-1"></i>
                                        {{ $row->penilaian_count > 0 ? 'Lanjutkan' : 'Mulai Nilai' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
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
