@php
    $current = request()->route()?->getName();
@endphp

<div class="nav-section">Menu Utama</div>
<a href="{{ route('penilai.dashboard') }}"
   class="nav-link {{ $current === 'penilai.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="nav-section">Penilaian</div>
<a href="{{ route('penilai.jadwal-visitasi.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'penilai.jadwal-visitasi') ? 'active' : '' }}">
    <i class="bi bi-calendar-week"></i> Jadwal Visitasi
</a>
<a href="{{ route('penilai.penilaian-visitasi.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'penilai.penilaian-visitasi') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Input Penilaian
</a>

<div class="nav-section">Hasil</div>
<a href="{{ route('hasil.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'hasil.') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i> Hasil Penilaian
</a>
<a href="#" class="nav-link"><i class="bi bi-printer"></i> Cetak Laporan</a>
