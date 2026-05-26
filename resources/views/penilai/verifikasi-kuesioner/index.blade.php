@extends('layouts.app')

@section('title', 'Verifikasi Kuesioner')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    @php
        $statusLabels = collect($statusOptions)->pluck('label', 'value');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Verifikasi Kuesioner</h1>
            <p class="text-secondary mb-0 small">Verifikasi jawaban kuesioner desa berdasarkan pertanyaan dan status jawaban.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('penilai.verifikasi-kuesioner.index') }}" class="row g-2 mb-3">
                <div class="col-md-9">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="belum" @selected($filters['status'] === 'belum')>Belum Diverifikasi</option>
                        @foreach ($statusOptions as $opt)
                            <option value="{{ $opt['value'] }}" @selected($filters['status'] === $opt['value'])>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('penilai.verifikasi-kuesioner.index') }}"
                       class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Desa</th>
                            <th>Pertanyaan</th>
                            <th>Jawaban</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $row)
                            <tr>
                                <td class="ps-3 small">
                                    {{ $row->tanggal_visitasi
                                        ? \Carbon\Carbon::parse($row->tanggal_visitasi)->translatedFormat('d M Y')
                                        : '-' }}
                                </td>
                                <td class="fw-medium">{{ $row->desa->nama }}</td>
                                <td>
                                    <div class="small fw-medium">{{ $row->kuesioner->pertanyaan }}</div>
                                    <code class="small text-secondary">{{ $row->kuesioner->kode_indikator }}</code>
                                </td>
                                <td>
                                    <div class="small">{{ $row->jawaban ?? '-' }}</div>
                                </td>
                                <td>
                                    @if ($row->status_jawaban === 'iya')
                                        <span class="badge bg-success-subtle text-success">Iya</span>
                                    @elseif ($row->status_jawaban === 'tidak')
                                        <span class="badge bg-danger-subtle text-danger">Tidak</span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @php
                                        $jadwal = $row->desa->jadwalVisitasi
                                            ->where('periode_id', $row->periode_id)
                                            ->first();
                                    @endphp
                                    @if ($jadwal)
                                        <a href="{{ route('penilai.verifikasi-kuesioner.edit', $jadwal) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-check-square me-1"></i>
                                            Verifikasi
                                        </a>
                                    @else
                                        <span class="text-secondary small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data verifikasi kuesioner.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="mt-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
@endsection
