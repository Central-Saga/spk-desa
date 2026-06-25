@extends('layouts.app')

@section('title', 'Penilaian Visitasi - ' . $jadwal->desa->nama)

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    @php
        $totalBobot = $template->sum('bobot');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Penilaian Visitasi</h1>
            <p class="text-secondary mb-0 small">
                <strong>{{ $jadwal->desa->nama }}</strong>
                &middot; {{ $jadwal->periode->nama }}
                &middot; {{ $jadwal->tanggal_visitasi->translatedFormat('d M Y') }}
                &middot; Petugas {{ $jadwal->petugas?->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('penilai.penilaian-visitasi.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="alert alert-info border-0 shadow-sm small">
        <i class="bi bi-info-circle me-2"></i>
        Total bobot indikator visitasi untuk <strong>{{ $jadwal->desa->nama }}</strong>:
        <strong>{{ number_format($totalBobot, 2) }} / 100</strong>.
        Skor 0 sampai 100 per indikator.
        @php
            $desaSpesifikCount = $template->where('desa_id', $jadwal->desa_id)->count();
            $globalCount = $template->whereNull('desa_id')->count();
        @endphp
        @if ($desaSpesifikCount > 0)
            <span class="ms-2 badge bg-info-subtle text-info border border-info-subtle">
                {{ $desaSpesifikCount }} indikator khusus
            </span>
        @elseif ($globalCount > 0)
            <span class="ms-2 badge bg-light text-secondary border">
                Indikator global
            </span>
        @endif
    </div>

    <form method="POST" action="{{ route('penilai.penilaian-visitasi.update', $jadwal) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                @foreach ($template as $idx => $item)
                    @php
                        $exist = $existing->get($item->indikator_visitasi);
                        $skorVal = old("penilaian.{$idx}.skor", $exist?->skor);
                        $ketVal = old("penilaian.{$idx}.keterangan", $exist?->keterangan);
                    @endphp

                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-md-7">
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <code class="small">{{ $item->kode }}</code>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    Bobot {{ number_format($item->bobot, 2) }}
                                </span>
                                @if ($item->desa_id === $jadwal->desa_id)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Khusus {{ $jadwal->desa->nama }}</span>
                                @else
                                    <span class="badge bg-light text-secondary border">Global</span>
                                @endif
                            </div>
                            <p class="mb-1 fw-medium">{{ $item->indikator_visitasi }}</p>
                            @if ($item->deskripsi)
                                <p class="text-secondary small mb-0">{{ $item->deskripsi }}</p>
                            @endif

                            <input type="hidden" name="penilaian[{{ $idx }}][indikator]" value="{{ $item->indikator_visitasi }}">
                            <input type="hidden" name="penilaian[{{ $idx }}][bobot]" value="{{ $item->bobot }}">
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

                            <label class="form-label small fw-medium mt-3">Bukti Gambar (boleh lebih dari 1)</label>
                            <input type="file"
                                   name="penilaian[{{ $idx }}][bukti_gambar][]"
                                   accept="image/*"
                                   multiple
                                   class="form-control form-control-sm @error('penilaian.'.$idx.'.bukti_gambar') is-invalid @enderror">
                            @error("penilaian.{$idx}.bukti_gambar")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($exist?->buktiGambar->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach ($exist->buktiGambar as $g)
                                        <div class="position-relative">
                                            <a href="{{ asset('storage/'.$g->path) }}" target="_blank">
                                                <img src="{{ asset('storage/'.$g->path) }}"
                                                     alt="Bukti #{{ $g->urutan }}"
                                                     class="img-thumbnail"
                                                     style="width: 90px; height: 90px; object-fit: cover;">
                                            </a>
                                            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger hapus-gambar-badge">
                                                <input type="checkbox"
                                                       name="penilaian[{{ $idx }}][hapus_gambar][]"
                                                       value="{{ $g->id }}"
                                                       class="form-check-input"
                                                       onchange="this.closest('.badge').classList.toggle('bg-danger', this.checked); this.closest('.badge').classList.toggle('bg-secondary', !this.checked)">
                                                <i class="bi bi-trash"></i>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-secondary small mt-1">Centang gambar untuk dihapus saat simpan.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-secondary small">
                    Sudah dinilai: <strong>{{ $existing->count() }}</strong> / {{ $template->count() }} indikator
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Penilaian
                </button>
            </div>
        </div>
    </form>
@endsection
