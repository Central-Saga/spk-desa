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
                            <th class="ps-3">Tgl Verifikasi</th>
                            <th>Desa</th>
                            <th>Pertanyaan</th>
                            <th>Jawaban</th>
                            <th>Status Verifikasi</th>
                            <th>Catatan</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $row)
                            <tr>
                                <td class="ps-3 small">
                                    {{ $row->tanggal_verifikasi
                                        ? $row->tanggal_verifikasi->translatedFormat('d M Y')
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
                                    @php
                                        $verifikasiStatus = $row->verifikasi_status ?? null;
                                    @endphp
                                    @if ($verifikasiStatus === 'disetujui')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Disetujui</span>
                                    @elseif ($verifikasiStatus === 'ditolak')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Ditolak</span>
                                    @elseif ($verifikasiStatus === 'perlu_perbaikan')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Perlu Perbaikan</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small" style="max-width: 200px;">
                                        {{ $row->verifikasi_catatan ?? '-' }}
                                    </div>
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
                                <td colspan="7" class="text-center text-secondary py-4">
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
