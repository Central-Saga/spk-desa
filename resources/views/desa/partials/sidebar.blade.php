@php
    $current = request()->route()?->getName();
@endphp

<div class="nav-section">Menu Utama</div>
<a href="{{ route('desa.dashboard') }}"
   class="nav-link {{ $current === 'desa.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="nav-section">Pengisian</div>
<a href="{{ route('desa.kuesioner.edit') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'desa.kuesioner') ? 'active' : '' }}">
    <i class="bi bi-pencil-square"></i> Isi Kuesioner
</a>
<a href="{{ route('desa.profil.edit') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'desa.profil') ? 'active' : '' }}">
    <i class="bi bi-house-door"></i> Profil Desa
</a>

<div class="nav-section">Hasil</div>
<a href="#" class="nav-link"><i class="bi bi-clipboard-data"></i> Hasil Penilaian</a>
<a href="#" class="nav-link"><i class="bi bi-printer"></i> Cetak Laporan</a>
