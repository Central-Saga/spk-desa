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
            <p class="text-secondary mb-0 small">Daftar desa dengan jawaban kuesioner final periode aktif. Klik Verifikasi untuk meninjau pertanyaan dan jawaban tiap desa.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('penilai.verifikasi-kuesioner.index') }}" class="row g-2 mb-3">
                <div class="col-md-9">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
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
                            <th class="ps-3">Tgl Verifikasi</th>
                            <th>Desa</th>
                            <th>Wilayah</th>
                            <th>Status Verifikasi</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $row)
                            @php
                                $desa = $desaMap->get($row->desa_id);
                                $totalPertanyaan = (int) $row->total_pertanyaan;
                                $totalDiverifikasi = (int) $row->total_diverifikasi;
                                $verifikasiStatus = $totalDiverifikasi === 0
                                    ? 'belum'
                                    : ($totalDiverifikasi >= $totalPertanyaan ? 'selesai' : 'sebagian');
                            @endphp
                            <tr>
                                <td class="ps-3 small">
                                    {{ $row->tanggal_verifikasi
                                        ? \Illuminate\Support\Carbon::parse($row->tanggal_verifikasi)->translatedFormat('d M Y')
                                        : '-' }}
                                </td>
                                <td class="fw-medium">{{ $desa?->nama ?? '-' }}</td>
                                <td>
                                    <div class="small text-secondary">
                                        {{ $desa?->kecamatan ?? '-' }}, {{ $desa?->kabupaten ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($verifikasiStatus === 'belum')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Belum</span>
                                    @elseif ($verifikasiStatus === 'sebagian')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Sebagian ({{ $totalDiverifikasi }}/{{ $totalPertanyaan }})</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('penilai.verifikasi-kuesioner.edit', [$row->desa_id, $row->periode_id]) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-check-square me-1"></i>
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
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
