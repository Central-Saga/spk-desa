@extends('laporan.pdf.layout', ['title' => 'Laporan Rekapitulasi'])

@section('content')
    <div class="judul">Laporan Rekapitulasi Penilaian</div>
    <div class="subjudul">
        Periode: <strong>{{ $periode->nama }}</strong>
        ({{ $periode->tanggal_mulai->translatedFormat('d M Y') }}
        &mdash; {{ $periode->tanggal_selesai->translatedFormat('d M Y') }})
    </div>

    @if ($hasil->isEmpty())
        <p class="text-center text-muted">Belum ada data hasil perhitungan untuk periode ini.</p>
    @else
        <table class="bordered">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px;">Peringkat</th>
                    <th>Nama Desa</th>
                    <th>Kabupaten</th>
                    <th>Kecamatan</th>
                    <th class="text-right" style="width: 90px;">Nilai Kuesioner (60%)</th>
                    <th class="text-right" style="width: 90px;">Nilai Visitasi (40%)</th>
                    <th class="text-right" style="width: 90px;">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hasil as $row)
                    <tr>
                        <td class="text-center">
                            @if ($row->peringkat == 1)
                                <span class="badge-rank">#1</span>
                            @else
                                #{{ $row->peringkat ?? '—' }}
                            @endif
                        </td>
                        <td><strong>{{ $row->desa->nama }}</strong></td>
                        <td>{{ $row->desa->kabupaten }}</td>
                        <td>{{ $row->desa->kecamatan }}</td>
                        <td class="text-right">{{ number_format($row->nilai_kuesioner, 2) }}</td>
                        <td class="text-right">{{ number_format($row->nilai_visitasi, 2) }}</td>
                        <td class="text-right"><strong>{{ number_format($row->nilai_akhir, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="row">
                <div class="label">Total desa dinilai</div>
                <div class="value">: {{ $hasil->count() }} desa</div>
            </div>
            <div class="row">
                <div class="label">Rata-rata nilai akhir</div>
                <div class="value">: {{ number_format($hasil->avg('nilai_akhir'), 2) }}</div>
            </div>
            <div class="row">
                <div class="label">Nilai tertinggi</div>
                <div class="value">: {{ number_format($hasil->max('nilai_akhir'), 2) }}
                    ({{ $hasil->sortByDesc('nilai_akhir')->first()?->desa?->nama }})</div>
            </div>
            <div class="row">
                <div class="label">Nilai terendah</div>
                <div class="value">: {{ number_format($hasil->min('nilai_akhir'), 2) }}</div>
            </div>
        </div>

        <div class="ttd">
            Denpasar, {{ $tanggalCetak->translatedFormat('d F Y') }}<br>
            <span class="ttd-line">Pejabat Berwenang</span>
        </div>
    @endif
@endsection
