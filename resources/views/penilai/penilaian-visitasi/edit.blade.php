@extends('layouts.app')

@section('title', 'Penilaian Visitasi - ' . $jadwal->desa->nama)

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    @php
        $totalBobot = collect($template)->sum('bobot');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Penilaian Visitasi</h1>
            <p class="text-secondary mb-0 small">
                <strong>{{ $jadwal->desa->nama }}</strong>
                &middot; {{ $jadwal->periode->nama }}
                &middot; {{ $jadwal->tanggal_visitasi->translatedFormat('d M Y') }}
                &middot; Petugas {{ $jadwal->petugas->name }}
            </p>
        </div>
        <a href="{{ route('penilai.penilaian-visitasi.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm small">
        <i class="bi bi-info-circle me-2"></i>
        Total bobot indikator visitasi: <strong>{{ number_format($totalBobot, 2) }} / 100</strong>.
        Skor 0 sampai 100 per indikator. Sistem akan menghitung weighted sum saat perhitungan nilai akhir.
    </div>

    <form method="POST" action="{{ route('penilai.penilaian-visitasi.update', $jadwal) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                @foreach ($template as $idx => $item)
                    @php
                        $exist = $existing->get($item['nama']);
                        $skorVal = old("penilaian.{$idx}.skor", $exist?->skor);
                        $ketVal = old("penilaian.{$idx}.keterangan", $exist?->keterangan);
                    @endphp

                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-md-7">
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <code class="small">{{ $item['kode'] }}</code>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    Bobot {{ number_format($item['bobot'], 2) }}
                                </span>
                            </div>
                            <p class="mb-1 fw-medium">{{ $item['nama'] }}</p>
                            <p class="text-secondary small mb-0">{{ $item['deskripsi'] }}</p>

                            <input type="hidden" name="penilaian[{{ $idx }}][indikator]" value="{{ $item['nama'] }}">
                            <input type="hidden" name="penilaian[{{ $idx }}][bobot]" value="{{ $item['bobot'] }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-medium">Skor (0-100) <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="penilaian[{{ $idx }}][skor]"
                                   value="{{ $skorVal }}"
                                   step="0.01" min="0" max="100"
                                   class="form-control @error('penilaian.'.$idx.'.skor') is-invalid @enderror"
                                   required>
                            @error("penilaian.{$idx}.skor")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-medium">Keterangan</label>
                            <input type="text"
                                   name="penilaian[{{ $idx }}][keterangan]"
                                   value="{{ $ketVal }}"
                                   class="form-control form-control-sm"
                                   placeholder="Opsional">

                            <label class="form-label small fw-medium mt-3">Bukti Gambar</label>
                            <input type="file"
                                   name="penilaian[{{ $idx }}][bukti_gambar]"
                                   accept="image/*"
                                   class="form-control form-control-sm @error('penilaian.'.$idx.'.bukti_gambar') is-invalid @enderror">
                            @error("penilaian.{$idx}.bukti_gambar")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($exist?->bukti_gambar)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/'.$exist->bukti_gambar) }}" target="_blank" class="small text-decoration-none">
                                        <i class="bi bi-image me-1"></i> Lihat bukti tersimpan
                                    </a>
                                    <img src="{{ asset('storage/'.$exist->bukti_gambar) }}"
                                         alt="Bukti gambar {{ $item['nama'] }}"
                                         class="img-thumbnail d-block mt-2 w-50">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-secondary small">
                    Sudah dinilai: <strong>{{ $existing->count() }}</strong> / {{ count($template) }} indikator
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Penilaian
                </button>
            </div>
        </div>
    </form>
@endsection
