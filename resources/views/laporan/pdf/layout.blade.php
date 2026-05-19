<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page { margin: 1.5cm 1cm; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header .institusi {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .alamat {
            font-size: 8pt;
            color: #475569;
        }
        .judul {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 14px 0 6px;
        }
        .subjudul {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.bordered th, table.bordered td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: top;
        }
        table thead {
            background: #e2e8f0;
        }
        table thead th { text-align: left; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }
        .summary .row { display: table-row; }
        .summary .label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding: 3px 0;
        }
        .summary .value {
            display: table-cell;
            padding: 3px 0;
        }
        .footer {
            margin-top: 24px;
            font-size: 9pt;
            color: #475569;
        }
        .ttd {
            margin-top: 30px;
            text-align: right;
        }
        .ttd-line {
            margin-top: 60px;
            border-top: 1px solid #1e293b;
            display: inline-block;
            min-width: 200px;
            padding-top: 4px;
            font-weight: bold;
        }
        .small { font-size: 9pt; }
        .text-muted { color: #64748b; }
        .badge-rank {
            background: #fbbf24;
            color: #1e293b;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="institusi">Komisi Informasi Provinsi Bali</div>
        <div class="alamat">Sistem Penilaian Kinerja Desa &mdash; Penilaian Apresiasi Desa</div>
    </div>

    @yield('content')

    <div class="footer text-muted">
        <div>Dicetak {{ $tanggalCetak->translatedFormat('d F Y H:i') }} oleh {{ $pencetak->name }} ({{ $pencetak->username }})</div>
    </div>
</body>
</html>
