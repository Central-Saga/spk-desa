@extends('laporan.pdf.layout', ['title' => 'Laporan Audit Trail'])

@section('content')
    <div class="judul">Laporan Audit Trail</div>
    <div class="subjudul">
        {{ $from->translatedFormat('d M Y') }} &mdash; {{ $to->translatedFormat('d M Y') }}
    </div>

    @if ($audit->isEmpty())
        <p class="text-center text-muted">Tidak ada catatan aktivitas pada rentang tanggal ini.</p>
    @else
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width: 110px;">Waktu</th>
                    <th style="width: 120px;">Pengguna</th>
                    <th style="width: 90px;">Aksi</th>
                    <th>Deskripsi</th>
                    <th style="width: 110px;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($audit as $row)
                    <tr>
                        <td class="small">{{ $row->created_at->translatedFormat('d M Y H:i') }}</td>
                        <td class="small">{{ $row->user?->name ?? '—' }}</td>
                        <td class="small">{{ ucfirst(str_replace('_', ' ', $row->aksi?->value ?? '')) }}</td>
                        <td class="small">{{ $row->deskripsi }}</td>
                        <td class="small">{{ $row->ip_address ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary" style="margin-top: 14px;">
            <div class="row">
                <div class="label">Total aktivitas</div>
                <div class="value">: {{ $audit->count() }} kejadian</div>
            </div>
        </div>
    @endif
@endsection
