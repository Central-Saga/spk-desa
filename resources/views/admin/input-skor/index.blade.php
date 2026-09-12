@extends('layouts.app')

@section('title', 'Input Skor Kuesioner')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Input Skor Kuesioner</h1>
            <p class="text-secondary mb-0 small">Periode: {{ $periode?->nama ?? 'Apresiasi Keterbukaan Informasi Publik Desa 2026' }}</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-semibold mb-0">Pilih Desa</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <th>Desa</th>
                            <th class="text-center">Skor Terisi</th>
                            <th class="text-center">Total Indikator</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $i => $row)
                            @php
                                $isLengkap = ($row['total'] > 0 && $row['terisi'] === $row['total']);
                                $persentase = $row['total'] > 0 ? round(($row['terisi'] / $row['total']) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="ps-3 text-secondary">{{ $i + 1 }}</td>
                                <td class="fw-medium">{{ $row['desa']->nama }}</td>
                                <td class="text-center">{{ $row['terisi'] }}</td>
                                <td class="text-center">{{ $row['total'] }}</td>
                                <td class="text-center">
                                    @if ($isLengkap)
                                        <span class="badge bg-success-subtle text-success px-2 py-1">Lengkap</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1">{{ $persentase }}%</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('desa.kuesioner.edit', ['desa_id' => $row['desa']->id]) }}"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-pencil-square me-1"></i> Input Skor
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data desa aktif.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-light border border-secondary-subtle d-flex align-items-start gap-3 shadow-sm rounded-3">
        <div class="text-danger fs-5 mt-n1">
            <i class="bi bi-info-circle-fill"></i>
        </div>
        <div class="small text-secondary">
            <strong>Petunjuk:</strong> Klik tombol <strong>Input Skor</strong> pada desa yang ingin diisi. Di halaman kuesioner, isi kolom Skor (0-100) untuk setiap indikator. Skor hanya bisa diisi oleh Super Admin.
        </div>
    </div>
@endsection
