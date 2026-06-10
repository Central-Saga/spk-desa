@extends('layouts.app')

@section('title', 'Isi Kuesioner Penilaian')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Isi Kuesioner Penilaian</h1>
            <p class="text-secondary mb-0 small">
                Desa: <strong>{{ $desa->nama }}</strong>
                &middot; Periode: <strong>{{ $periode->nama }}</strong>
            </p>
        </div>
        <div class="text-end">
            <div class="small text-secondary">Total bobot</div>
            <div class="fs-5 fw-semibold">{{ number_format($totalBobot, 2) }} / 100</div>
        </div>
    </div>

    @if ($isFinal)
        <div class="alert alert-warning border-0 shadow-sm" role="alert">
            <i class="bi bi-lock-fill me-2"></i>
            <strong>Jawaban sudah difinalisasi.</strong>
            Untuk mengubah, hubungi Super Admin.
        </div>
    @endif

    <form method="POST" action="{{ route('desa.kuesioner.update') }}" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="periode_id" value="{{ $periode->id }}">

        @php $idx = 0; @endphp

        @forelse ($byKategori as $kategori => $items)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h2 class="h6 fw-semibold mb-0">
                        <i class="bi bi-tag text-primary me-2"></i>{{ $kategori }}
                    </h2>
                </div>
                <div class="card-body">
                    @foreach ($items as $item)
                        @php
                            $jawaban = $jawabanTersimpan->get($item->id);
                            $skorVal = old("jawaban.{$idx}.skor", $jawaban?->skor);
                            $jawabanVal = old("jawaban.{$idx}.jawaban", $jawaban?->jawaban);
                            $ketVal = old("jawaban.{$idx}.keterangan", $jawaban?->keterangan);
                        @endphp
                        <div class="row g-3 mb-3 pb-3 border-bottom">
                            <div class="col-md-7">
                                <div class="d-flex gap-2 align-items-start mb-2">
                                    <code class="small">{{ $item->kode_indikator }}</code>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        Bobot {{ number_format($item->bobot_indikator, 2) }}
                                    </span>
                                </div>
                                <p class="mb-2 fw-medium">{{ $item->pertanyaan }}</p>

                                <input type="hidden"
                                       name="jawaban[{{ $idx }}][kuesioner_id]"
                                       value="{{ $item->id }}">

                                <textarea name="jawaban[{{ $idx }}][jawaban]"
                                          rows="2"
                                          class="form-control form-control-sm @error('jawaban.'.$idx.'.jawaban') is-invalid @enderror"
                                          placeholder="Jawaban deskriptif (opsional)"
                                          @if ($isFinal) readonly @endif>{{ $jawabanVal }}</textarea>
                                @error("jawaban.{$idx}.jawaban")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                @if (auth()->user()->isSuperAdmin())
                                    <label class="form-label small fw-medium">Skor (0-100)</label>
                                    <input type="number"
                                           name="jawaban[{{ $idx }}][skor]"
                                           value="{{ $skorVal }}"
                                           step="0.01" min="0" max="100"
                                           class="form-control @error('jawaban.'.$idx.'.skor') is-invalid @enderror"
                                           @if ($isFinal) readonly @endif>
                                    @error("jawaban.{$idx}.skor")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <label class="form-label small fw-medium">Skor</label>
                                    <div class="form-control-plaintext text-secondary small py-1">
                                        @if ($jawaban?->skor !== null)
                                            {{ number_format($jawaban->skor, 2) }}
                                        @else
                                            <em class="text-muted">Diisi oleh Super Admin</em>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-medium">Keterangan</label>
                                <input type="text"
                                       name="jawaban[{{ $idx }}][keterangan]"
                                       value="{{ $ketVal }}"
                                       class="form-control form-control-sm"
                                       @if ($isFinal) readonly @endif>
                            </div>
                        </div>
                        @php $idx++; @endphp
                    @endforeach
                </div>
            </div>
        @empty
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>
                Belum ada indikator kuesioner untuk periode ini. Hubungi Super Admin.
            </div>
        @endforelse

        @if ($byKategori->isNotEmpty() && ! $isFinal)
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-secondary small">
                        Sudah diisi: <strong>{{ $sudahDijawab }}</strong> / {{ $totalIndikator }} indikator
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="finalisasi" value="0" class="btn btn-outline-primary">
                            <i class="bi bi-save me-1"></i> Simpan Draft
                        </button>
                        <button type="submit" name="finalisasi" value="1" class="btn btn-primary"
                                onclick="return confirm('Finalisasi akan mengunci jawaban dan tidak dapat diubah lagi tanpa bantuan Super Admin. Lanjutkan?');">
                            <i class="bi bi-check2-circle me-1"></i> Submit Final
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </form>
@endsection
