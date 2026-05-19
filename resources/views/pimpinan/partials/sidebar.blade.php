@php
    $current = request()->route()?->getName();
@endphp

<div class="nav-section">Menu Utama</div>
<a href="{{ route('pimpinan.dashboard') }}"
   class="nav-link {{ $current === 'pimpinan.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="nav-section">Monitoring</div>
<a href="{{ route('hasil.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'hasil.') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i> Hasil Penilaian
</a>
<a href="#" class="nav-link"><i class="bi bi-printer"></i> Cetak Laporan</a>
