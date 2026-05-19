@php
    $current = request()->route()?->getName();
@endphp

<div class="nav-section">Menu Utama</div>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ $current === 'admin.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<div class="nav-section">Master Data</div>
<a href="{{ route('admin.pengguna.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.pengguna') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Pengguna
</a>
<a href="{{ route('admin.desa.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.desa') ? 'active' : '' }}">
    <i class="bi bi-house-door"></i> Desa
</a>
<a href="{{ route('admin.periode.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.periode') ? 'active' : '' }}">
    <i class="bi bi-calendar-event"></i> Periode
</a>
<a href="{{ route('admin.kuesioner.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.kuesioner') ? 'active' : '' }}">
    <i class="bi bi-list-check"></i> Kuesioner
</a>

<div class="nav-section">Penilaian</div>
<a href="{{ route('penilai.jadwal-visitasi.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'penilai.jadwal-visitasi') ? 'active' : '' }}">
    <i class="bi bi-calendar-week"></i> Jadwal Visitasi
</a>
<a href="{{ route('penilai.penilaian-visitasi.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'penilai.penilaian-visitasi') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Input Penilaian Visitasi
</a>
<a href="{{ route('hasil.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'hasil.') ? 'active' : '' }}">
    <i class="bi bi-clipboard-data"></i> Hasil Penilaian
</a>
<a href="{{ route('admin.nilai-akhir.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.nilai-akhir') ? 'active' : '' }}">
    <i class="bi bi-trophy"></i> Nilai Akhir
</a>

<div class="nav-section">Lainnya</div>
<a href="{{ route('laporan.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'laporan.') ? 'active' : '' }}">
    <i class="bi bi-printer"></i> Cetak Laporan
</a>
<a href="{{ route('admin.audit-trail.index') }}"
   class="nav-link {{ str_starts_with($current ?? '', 'admin.audit-trail') ? 'active' : '' }}">
    <i class="bi bi-shield-check"></i> Audit Trail
</a>
