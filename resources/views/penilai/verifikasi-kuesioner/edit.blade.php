@extends('layouts.app')

@section('title', 'Verifikasi Kuesioner - ' . $jadwal->desa->nama)

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    @php
        $totalDiverifikasi = $verifikasiExisting->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Verifikasi Kuesioner</h1>
            <p class="text-secondary mb-0 small">
                <strong>{{ $jadwal->desa->nama }}</strong>
                &middot; {{ $jadwal->periode->nama }}
                &middot; {{ $jadwal->tanggal_visitasi->translatedFormat('d M Y') }}
                &middot; Petugas {{ $jadwal->petugas->name }}
            </p>
        </div>
        <a href="{{ route('penilai.verifikasi-kuesioner.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm small">
        <i class="bi bi-info-circle me-2"></i>
        Verifikasi setiap pertanyaan kuesioner terhadap kondisi lapangan.
        Total bobot kuesioner: <strong>{{ number_format($totalBobot, 2) }} / 100</strong>.
    </div>

    <form method="POST" action="{{ route('penilai.verifikasi-kuesioner.update', $jadwal) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                @forelse ($kuesionerList as $idx => $kues)
                    @php
                        $jawabanDesaItem = $jawabanDesa->get($kues->id);
                        $verifikasiItem = $verifikasiExisting->get($kues->id);
                        $statusVal = old("verifikasi.{$idx}.status_verifikasi", $verifikasiItem?->status_verifikasi);
                        $catatanVal = old("verifikasi.{$idx}.catatan", $verifikasiItem?->catatan);
                    @endphp

                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-md-7">
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <code class="small">{{ $kues->kode_indikator }}</code>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    Bobot {{ number_format($kues->bobot_indikator, 2) }}
                                </span>
                                <span class="badge bg-info-subtle text-info">
                                    {{ $kues->kategori }}
                                </span>
                            </div>
                            <p class="mb-1 fw-medium">{{ $kues->pertanyaan }}</p>

                            {{-- Jawaban Desa --}}
                            @if ($jawabanDesaItem)
                                <div class="mt-2 p-2 bg-light rounded border small">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="text-secondary">Jawaban Desa:</span>
                                        @if ($jawabanDesaItem->status_jawaban === 'iya')
                                            <span class="badge bg-success-subtle text-success">Iya</span>
                                        @elseif ($jawabanDesaItem->status_jawaban === 'tidak')
                                            <span class="badge bg-danger-subtle text-danger">Tidak</span>
                                        @endif
                                        <span class="text-secondary">Skor: {{ number_format($jawabanDesaItem->skor, 2) }}</span>
                                        <span class="badge bg-{{ $jawabanDesaItem->status->value === 'final' ? 'success' : 'warning' }}">{{ $jawabanDesaItem->status->label() }}</span>
                                    </div>
                                    <div class="fw-medium">{{ $jawabanDesaItem->jawaban ?? '-' }}</div>
                                </div>
                            @else
                                <div class="mt-2 small text-secondary">
                                    <i class="bi bi-exclamation-circle me-1"></i> Desa belum mengisi kuesioner ini.
                                </div>
                            @endif

                            <input type="hidden" name="verifikasi[{{ $idx }}][kuesioner_id]"
                                   value="{{ $kues->id }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Status Verifikasi
                                <span class="text-danger">*</span></label>
                            <select name="verifikasi[{{ $idx }}][status_verifikasi]"
                                    class="form-select @error("verifikasi.{$idx}.status_verifikasi") is-invalid @enderror"
                                    required>
                                <option value="">-- Pilih --</option>
                                @foreach ($statusOptions as $opt)
                                    <option value="{{ $opt['value'] }}" @selected($statusVal === $opt['value'])>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error("verifikasi.{$idx}.status_verifikasi")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Catatan Verifikasi</label>
                            <textarea name="verifikasi[{{ $idx }}][catatan]"
                                      rows="2"
                                      class="form-control form-control-sm"
                                      placeholder="Catatan hasil verifikasi lapangan...">{{ $catatanVal }}</textarea>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-secondary py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Belum ada pertanyaan kuesioner untuk periode ini.
                    </div>
                @endforelse
            </div>
        </div>

        @if ($kuesionerList->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-secondary small">
                        Sudah diverifikasi: <strong>{{ $totalDiverifikasi }}</strong> / {{ $kuesionerList->count() }} pertanyaan
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Verifikasi
                    </button>
                </div>
            </div>
        @endif
    </form>
@endsection
