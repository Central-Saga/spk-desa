@extends('laporan.pdf.layout', ['title' => 'Laporan Per Desa - ' . $nilai->desa->nama])

@section('content')
    <div class="judul">Laporan Hasil Penilaian Desa</div>
    <div class="subjudul">{{ $nilai->desa->nama }} &middot; {{ $nilai->periode->nama }}</div>

    <div class="summary">
        <div class="row">
            <div class="label">Nama Desa</div>
            <div class="value">: {{ $nilai->desa->nama }}</div>
        </div>
        <div class="row">
            <div class="label">Kecamatan / Kabupaten</div>
            <div class="value">: {{ $nilai->desa->kecamatan }} / {{ $nilai->desa->kabupaten }}</div>
        </div>
        <div class="row">
            <div class="label">Kepala Desa</div>
            <div class="value">: {{ $nilai->desa->kepala_desa ?? '—' }}</div>
        </div>
        <div class="row">
            <div class="label">Periode Penilaian</div>
            <div class="value">: {{ $nilai->periode->nama }}</div>
        </div>
        <div class="row">
            <div class="label">Tanggal Penilaian</div>
            <div class="value">: {{ $nilai->periode->tanggal_mulai->translatedFormat('d M Y') }}
                &mdash; {{ $nilai->periode->tanggal_selesai->translatedFormat('d M Y') }}</div>
        </div>
    </div>

    <table class="bordered" style="margin-top: 14px;">
        <thead>
            <tr>
                <th colspan="2" class="text-center">Ringkasan Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Nilai Kuesioner (bobot 60%)</td>
                <td class="text-right" style="width: 100px;">{{ number_format($nilai->nilai_kuesioner, 2) }}</td>
            </tr>
            <tr>
                <td>Nilai Visitasi (bobot 40%)</td>
                <td class="text-right">{{ number_format($nilai->nilai_visitasi, 2) }}</td>
            </tr>
            <tr style="background: #f1f5f9;">
                <td><strong>Nilai Akhir</strong></td>
                <td class="text-right"><strong>{{ number_format($nilai->nilai_akhir, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Peringkat</td>
                <td class="text-right"><strong>#{{ $nilai->peringkat ?? '—' }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h3 style="font-size: 11pt; margin-top: 18px;">A. Detail Jawaban Kuesioner</h3>
    @if ($jawaban->isEmpty())
        <p class="text-muted">Tidak ada jawaban kuesioner.</p>
    @else
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 80px;">Jawaban</th>
                    <th style="width: 50px;">Kode</th>
                    <th>Pertanyaan</th>
                    <th style="width: 60px;">Status</th>
                    <th class="text-right" style="width: 60px;">Bobot</th>
                    <th class="text-right" style="width: 70px;">Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jawaban as $j)
                    @php
                        $bobot = (float) ($j->kuesioner->bobot_indikator ?? 0);
                        $kontribusi = ((float) $j->skor * $bobot) / 100;
                        $statusLabel = match ($j->status_jawaban) {
                            'iya' => 'Iya',
                            'tidak' => 'Tidak',
                            default => '—',
                        };
                    @endphp
                    <tr>
                        <td class="small">{{ \Illuminate\Support\Str::limit($j->jawaban, 50) ?: '—' }}</td>
                        <td>{{ $j->kuesioner?->kode_indikator ?? '—' }}</td>
                        <td class="small">{{ $j->kuesioner?->pertanyaan ?? '—' }}</td>
                        <td>{{ $statusLabel }}</td>
                        <td class="text-right">{{ number_format($bobot, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($kontribusi, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3 style="font-size: 11pt; margin-top: 18px;">B. Detail Penilaian Visitasi</h3>
    @if ($visitasi->isEmpty())
        <p class="text-muted">Tidak ada penilaian visitasi.</p>
    @else
        <table class="bordered">
            <thead>
                <tr>
                    <th>Indikator</th>
                    <th>Keterangan</th>
                    <th class="text-right" style="width: 60px;">Skor</th>
                    <th class="text-right" style="width: 60px;">Bobot</th>
                    <th class="text-right" style="width: 70px;">Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($visitasi as $v)
                    @php $kontribusi = ((float) $v->skor * (float) $v->bobot) / 100; @endphp
                    <tr>
                        <td><strong>{{ $v->indikator_visitasi }}</strong></td>
                        <td class="small">{{ $v->keterangan ?: '—' }}</td>
                        <td class="text-right">{{ number_format($v->skor, 2) }}</td>
                        <td class="text-right">{{ number_format($v->bobot, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($kontribusi, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="ttd">
        Denpasar, {{ $tanggalCetak->translatedFormat('d F Y') }}<br>
        <span class="ttd-line">Pejabat Berwenang</span>
    </div>
@endsection
