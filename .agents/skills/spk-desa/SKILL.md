---
name: spk-desa
description: "Gunakan skill ini saat mengerjakan fitur Sistem Informasi Penilaian Kinerja Desa (SPK Desa) untuk Komisi Informasi Provinsi Bali — pengelolaan data pengguna multi-role, master data desa, kuesioner penilaian, jadwal visitasi, input penilaian visitasi lapangan, perhitungan nilai akhir otomatis (bobot 60% kuesioner + 40% visitasi), pemeringkatan desa, pencetakan laporan PDF, dan audit trail aktivitas pengguna. Juga berlaku saat menangani autentikasi role-based (Super Admin / Staff Admin Desa / Staff Penilaian Komisi Informasi / Pimpinan Komisi Informasi) di Laravel 13 + Bootstrap 5 + MySQL 8.4 via Laravel Sail."
---

# SPK Desa — Deep Reference

Sistem Informasi Penilaian Kinerja Desa berbasis web untuk Komisi Informasi Provinsi Bali. Mendukung pelaksanaan Penilaian Apresiasi Desa secara terintegrasi mulai dari pengisian kuesioner, penilaian visitasi lapangan, perhitungan nilai akhir otomatis, hingga pelaporan hasil.

## Identitas & Stack (Non-Negotiable)

- **Backend**: Laravel v13 + PHP 8.5 (monolitik server-rendered, **bukan** API+SPA)
- **View**: Blade + Bootstrap 5 + Bootstrap Icons (**bukan** Tailwind/Filament/Livewire)
- **Auth**: Laravel default session-based + middleware role guard (RBAC custom atau `spatie/laravel-permission` jika scope diperluas)
- **Database**: MySQL 8.4 (utf8mb4_unicode_ci) — schema name `spk_desa`
- **Cache/Queue/Session**: Redis 7 (queue + cache), DB driver untuk session boleh dipakai bila Redis belum dipasang
- **Mail (dev)**: Mailpit
- **PDF**: `barryvdh/laravel-dompdf` untuk cetak laporan (Browsershot opsional kalau perlu chart hi-fi)
- **Build Tool**: Vite + SCSS (`resources/sass/app.scss`)
- **Testing**: Pest v4 + Black Box per fitur + UAT untuk akseptasi pengguna
- **Dev Env**: Laravel Sail di OrbStack — services `laravel.test` (port 8080), `mysql` (3306), `redis` (6379), `mailpit` (8025)

### Override Rules (project-level menang atas user-global)

User global AGENTS.md menyebut stack Filament 5 + PostgreSQL 16. **Untuk project ini abaikan itu.** Ikuti stack di atas: Bootstrap 5 + MySQL 8.4 + Blade biasa.

## Role Matrix (Authoritative)

4 role sesuai analisis BAB IV. Seeder database WAJIB membuat 4 role ini.

| Role | Slug | Scope Akses |
|---|---|---|
| **Super Admin** | `super_admin` | Akses penuh: kelola pengguna + kelola desa + kelola kuesioner + kelola jadwal visitasi + trigger hitung nilai akhir + lihat semua hasil + cetak semua laporan |
| **Staff Admin Desa** | `staff_admin_desa` | Kelola data desanya sendiri + isi kuesioner desanya + lihat hasil penilaian desanya + cetak laporan desanya |
| **Staff Penilaian Komisi Informasi** | `staff_penilaian` | Kelola jadwal visitasi + input penilaian visitasi lapangan + lihat hasil penilaian + cetak laporan sesuai kewenangannya |
| **Pimpinan Komisi Informasi** | `pimpinan` | Read-only: lihat hasil penilaian + cetak laporan untuk monitoring/evaluasi/pengambilan keputusan |

**Kunci RBAC:**
- Staff Admin Desa terikat ke **satu** desa (foreign key `users.desa_id`).
- Akses cross-desa Staff Admin Desa **DITOLAK** di policy/middleware.
- Pimpinan **tidak boleh** edit data, hanya `view` + `print`.
- Super Admin satu-satunya yang boleh trigger perhitungan nilai akhir (mencegah race condition multi-trigger).

## Entity Model (9 Entitas)

```
users               (id, name, username, email, password, role_id, desa_id?, is_active, timestamps)
                    └── role_id → roles, desa_id nullable (hanya wajib untuk Staff Admin Desa)
roles               (id, name, slug, description)
                    seed: super_admin, staff_admin_desa, staff_penilaian, pimpinan

desa                (id, nama, alamat, kecamatan, kabupaten, kode_pos?, telepon?,
                     email?, kepala_desa?, jumlah_penduduk?, is_active, timestamps)

periode_penilaian   (id, tahun, nama, tanggal_mulai, tanggal_selesai,
                     status [draft|aktif|selesai], timestamps)
                    └── 1 tahun bisa punya banyak periode bila perlu

kuesioner           (id, periode_id, kategori, kode_indikator, pertanyaan,
                     bobot_indikator (decimal 5,2), urutan, is_active, timestamps)
                    └── periode_id → periode_penilaian
                    UNIQUE (periode_id, kode_indikator)

jawaban_kuesioner   (id, desa_id, kuesioner_id, periode_id, jawaban, skor (decimal 5,2),
                     keterangan?, diisi_oleh, timestamps)
                    UNIQUE (desa_id, kuesioner_id, periode_id)

jadwal_visitasi     (id, desa_id, periode_id, tanggal_visitasi, waktu_mulai, waktu_selesai?,
                     lokasi, status [terjadwal|berlangsung|selesai|dibatalkan],
                     catatan?, dibuat_oleh, timestamps)

penilaian_visitasi  (id, jadwal_id, desa_id, periode_id, indikator_visitasi,
                     skor (decimal 5,2), bobot (decimal 5,2), keterangan?,
                     dinilai_oleh, tanggal_input, timestamps)

nilai_akhir         (id, desa_id, periode_id, nilai_kuesioner (decimal 5,2),
                     nilai_visitasi (decimal 5,2), nilai_akhir (decimal 5,2),
                     peringkat (int), dihitung_pada, dihitung_oleh, timestamps)
                    UNIQUE (desa_id, periode_id)

audit_trail         (id, user_id, aksi, model_type?, model_id?, deskripsi,
                     ip_address?, user_agent?, payload (json)?, created_at)
                    INDEX (user_id, created_at), INDEX (model_type, model_id)
```

**Kunci desain:**
- Semua data penilaian terikat `periode_penilaian` agar bisa multi-tahun + tetap bisa lihat histori.
- `kuesioner.bobot_indikator` total per periode harus = 100 (validasi di FormRequest + database trigger opsional).
- `jawaban_kuesioner.skor` = nilai mentah hasil pengisian (0–100), **bukan** sudah dikalikan bobot.
- `penilaian_visitasi.bobot` per indikator visitasi total juga harus = 100 per periode.
- `nilai_akhir.nilai_akhir = (nilai_kuesioner × 0.6) + (nilai_visitasi × 0.4)`.
- `audit_trail.payload` simpan JSON before/after untuk perubahan data sensitif.
- Soft delete (`SoftDeletes` trait) WAJIB untuk `desa`, `users`, `kuesioner` — data historis tidak boleh hilang.

## Expanded Use Case (Acceptance Criteria per Fitur)

Bagian ini berisi spesifikasi formal alur interaksi aktor ↔ sistem untuk setiap use case utama. **Gunakan sebagai acceptance criteria saat menulis controller, FormRequest, dan test Pest.** Setiap kasus mencakup prasyarat, alur utama (happy path), alur alternatif (sad path), dan pasca-kondisi.

### UC-01: Login

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin, Staff Admin Desa, Staff Penilaian, Pimpinan |
| **Tujuan** | Autentikasi pengguna + tetapkan hak akses berdasarkan role |
| **Prasyarat** | Pengguna telah memiliki akun terdaftar (`users.is_active = true`) |
| **Pemicu** | Pengguna membuka halaman login + submit credential |
| **Alur Utama** | 1. Pengguna buka `/login` → 2. Input `username` + `password` → 3. Sistem validasi format → 4. Sistem cek match credential di DB → 5. Sistem regenerate session + redirect ke dashboard sesuai role |
| **Alur Alternatif** | A1. Credential salah → tampilkan error "Kredensial tidak valid" + preserve `username` input. A2. Akun nonaktif (`is_active = false`) → logout otomatis + tampilkan "Akun Anda telah dinonaktifkan" |
| **Pasca-Kondisi** | Session aktif + `audit_trail` mencatat aksi `login` + redirect ke dashboard role |

**Test wajib:** happy path per role, credential salah, akun nonaktif, CSRF token absent, rate limiting (max 5 attempt / menit per IP).

---

### UC-02: Kelola Data Pengguna

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin |
| **Tujuan** | CRUD akun pengguna sistem |
| **Prasyarat** | Super Admin sudah login |
| **Pemicu** | Super Admin pilih menu "Data Pengguna" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tampilkan list user (paginated) → 3. Pilih aksi tambah/ubah/hapus → 4. Submit form → 5. Sistem validasi (FormRequest) → 6. Sistem persist ke DB → 7. Redirect dengan flash success |
| **Alur Alternatif** | A1. Field wajib kosong → return back dengan validation error. A2. `username` / `email` sudah dipakai → error "sudah digunakan". A3. Hapus user yang punya relasi (`jawaban_kuesioner`, `penilaian_visitasi`, `audit_trail`) → blokir hard delete, tawarkan deactivate (`is_active = false`) |
| **Pasca-Kondisi** | Data user ter-create/update/deactivate + `audit_trail` mencatat aksi |

**Validasi spesifik:**
- `username` unique case-insensitive, alphanumeric + underscore, 3–50 karakter.
- `email` unique + format valid.
- `password` minimal 8 karakter saat create; saat update opsional (kosong = tidak ubah).
- `role_id` wajib + harus exist.
- Bila `role.slug = staff_admin_desa` → `desa_id` WAJIB; selain itu `desa_id` HARUS null.

---

### UC-03: Kelola Data Desa

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin (full CRUD), Staff Admin Desa (update only, scope desanya) |
| **Tujuan** | Kelola identitas desa objek penilaian |
| **Prasyarat** | Pengguna sudah login |
| **Pemicu** | Pengguna pilih menu "Data Desa" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tampilkan list desa (Super Admin: semua; Staff Admin Desa: 1 record desanya) → 3. Pilih aksi tambah/ubah/lihat → 4. Submit form → 5. Sistem validasi → 6. Sistem persist → 7. Redirect dengan flash success |
| **Alur Alternatif** | A1. Field wajib kosong → validation error. A2. Staff Admin Desa coba edit desa lain → 403 via `DesaPolicy::update`. A3. Staff Admin Desa coba aksi `create` / `destroy` → 403 (tidak diizinkan) |
| **Pasca-Kondisi** | Data desa tersimpan + `audit_trail` mencatat |

**Policy `DesaPolicy::update`:**
```php
public function update(User $user, Desa $desa): bool
{
    return match ($user->role->slug) {
        'super_admin'      => true,
        'staff_admin_desa' => $user->desa_id === $desa->id,
        default            => false,
    };
}
```

---

### UC-04: Kelola Data Kuesioner

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin |
| **Tujuan** | Kelola indikator + pertanyaan + bobot kuesioner per periode |
| **Prasyarat** | Super Admin sudah login + minimal 1 `periode_penilaian` ada |
| **Pemicu** | Super Admin pilih menu "Kuesioner" |
| **Alur Utama** | 1. Pilih periode → 2. Sistem tampilkan list indikator periode tersebut + total bobot saat ini → 3. Pilih aksi tambah/ubah/hapus → 4. Submit form → 5. Sistem validasi (termasuk total bobot ≤ 100) → 6. Sistem persist → 7. Redirect dengan flash success |
| **Alur Alternatif** | A1. Field kosong → validation error. A2. Total `bobot_indikator` periode > 100 → tolak penyimpanan dengan pesan "Total bobot melebihi 100". A3. `kode_indikator` duplikat dalam periode → error unique. A4. Hapus indikator yang sudah ada `jawaban_kuesioner` → blokir, tawarkan soft delete |
| **Pasca-Kondisi** | Indikator tersimpan + total bobot terupdate + `audit_trail` mencatat |

**Validasi tambahan:**
- Periode dengan `status = aktif` tidak boleh diubah indikatornya bila sudah ada `jawaban_kuesioner` (mencegah inconsistency hasil).
- Periode bisa "dikunci" (`status = selesai`) → semua indikator read-only.

---

### UC-05: Isi Kuesioner

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Staff Admin Desa |
| **Tujuan** | Submit jawaban kuesioner periode aktif untuk desa terkait |
| **Prasyarat** | Login sebagai Staff Admin Desa + ada periode dengan `status = aktif` + `users.desa_id` tidak null |
| **Pemicu** | Staff Admin Desa pilih menu "Isi Kuesioner" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tampilkan semua indikator periode aktif + jawaban tersimpan (jika sudah pernah save draft) → 3. Pengguna isi `jawaban` + `skor` (0–100) + `keterangan` opsional per indikator → 4. Pilih "Simpan Draft" atau "Submit Final" → 5. Sistem validasi semua field → 6. Sistem persist via `JawabanKuesionerService::simpan` → 7. Tampilkan notifikasi sukses |
| **Alur Alternatif** | A1. Submit Final tapi ada indikator `skor` kosong → tampilkan peringatan "Masih ada N indikator belum diisi". A2. `skor < 0` atau `skor > 100` → validation error. A3. Periode `selesai` → form read-only. A4. Sudah Submit Final → form read-only kecuali Super Admin unlock |
| **Pasca-Kondisi** | `jawaban_kuesioner` tersimpan dengan UNIQUE `(desa_id, kuesioner_id, periode_id)` + `audit_trail` mencatat |

**Status flow jawaban:**
```
[Form Kosong] ──save draft──→ [Draft] ──save draft──→ [Draft]
                                  │
                                  └──submit final──→ [Final, read-only]
                                                          │
                                                          └──Super Admin unlock──→ [Draft]
```

---

### UC-06: Kelola Jadwal Visitasi

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin, Staff Penilaian |
| **Tujuan** | Atur jadwal kunjungan lapangan ke desa |
| **Prasyarat** | Login + ada minimal 1 `desa` aktif + ada `periode_penilaian` aktif |
| **Pemicu** | Pengguna pilih menu "Jadwal Visitasi" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tampilkan list jadwal (filter periode + status) → 3. Pilih aksi tambah/ubah → 4. Isi `desa_id`, `tanggal_visitasi`, `waktu_mulai`, `waktu_selesai`, `lokasi`, `petugas` → 5. Sistem validasi (termasuk konflik jadwal petugas) → 6. Sistem persist → 7. Redirect dengan flash success |
| **Alur Alternatif** | A1. Field wajib kosong → validation error. A2. Petugas yang sama sudah punya jadwal di rentang waktu beririsan → tampilkan peringatan "Petugas X sudah punya jadwal di waktu yang sama". A3. `tanggal_visitasi` di masa lalu untuk status `terjadwal` → validation error. A4. `desa_id` sudah punya jadwal `selesai` di periode tersebut → konfirmasi sebelum tambah jadwal kedua |
| **Pasca-Kondisi** | `jadwal_visitasi` tersimpan + petugas mendapat notifikasi (opsional, via mail queue) + `audit_trail` mencatat |

**Validasi konflik jadwal:**
```php
$adaKonflik = JadwalVisitasi::query()
    ->whereHas('petugas', fn ($q) => $q->where('users.id', $petugasId))
    ->whereDate('tanggal_visitasi', $tanggalBaru)
    ->where(function ($q) use ($mulai, $selesai) {
        $q->whereBetween('waktu_mulai', [$mulai, $selesai])
          ->orWhereBetween('waktu_selesai', [$mulai, $selesai]);
    })
    ->exists();
```

---

### UC-07: Input Penilaian Visitasi

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Staff Penilaian |
| **Tujuan** | Input skor hasil observasi lapangan setelah visitasi |
| **Prasyarat** | Login sebagai Staff Penilaian + ada `jadwal_visitasi` dengan `status = selesai` untuk desa terkait |
| **Pemicu** | Staff Penilaian pilih menu "Penilaian Visitasi" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tampilkan list desa yang jadwal visitasinya `selesai` di periode aktif tapi belum dinilai → 3. Pilih desa → 4. Sistem tampilkan form indikator visitasi + bobot → 5. Input `skor` (0–100) per indikator + `keterangan` opsional → 6. Submit → 7. Sistem validasi semua indikator terisi → 8. Sistem persist → 9. Notifikasi sukses |
| **Alur Alternatif** | A1. Desa belum punya `jadwal_visitasi` `selesai` → blokir akses form penilaian + pesan "Visitasi belum dilaksanakan". A2. Skor di luar 0–100 → validation error. A3. Sudah pernah dinilai → form populated, edit (audit trail catat perubahan). A4. Total bobot indikator visitasi periode ≠ 100 → blokir submit + tampilkan peringatan ke Super Admin |
| **Pasca-Kondisi** | `penilaian_visitasi` tersimpan untuk semua indikator + `audit_trail` mencatat + status jadwal otomatis berubah ke `selesai` (jika belum) |

**Catatan integritas:** indikator visitasi disimpan terpisah dari kuesioner — tabel `kuesioner` HANYA untuk pengisian Staff Admin Desa. Indikator visitasi hardcoded di config atau seeder per periode (bisa dimasukkan ke tabel `kuesioner` dengan flag `tipe = visitasi` jika scope perlu DRY).

---

### UC-08: Hitung Nilai Akhir

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Super Admin |
| **Tujuan** | Compute nilai akhir = (60% × kuesioner) + (40% × visitasi) + tetapkan ranking |
| **Prasyarat** | Periode aktif punya minimal 1 desa dengan `jawaban_kuesioner` + `penilaian_visitasi` lengkap |
| **Pemicu** | Super Admin klik tombol "Hitung Nilai Akhir" di menu nilai akhir |
| **Alur Utama** | 1. Buka menu → 2. Pilih periode → 3. Sistem tampilkan ringkasan kelengkapan data per desa → 4. Klik "Hitung" → 5. Sistem load semua jawaban + nilai visitasi → 6. Sistem hitung weighted sum per desa via `PerhitunganNilaiService::hitungSatuDesa` → 7. Sistem sort desc + assign `peringkat` 1, 2, 3, ... → 8. Sistem persist `nilai_akhir` (UPSERT) → 9. Tampilkan tabel hasil + ranking |
| **Alur Alternatif** | A1. Ada desa dengan kuesioner / visitasi belum lengkap → tampilkan modal konfirmasi: "X desa belum lengkap, lanjutkan?". Pilihan: skip desa tsb dari ranking ATAU hitung dengan nilai 0 (default skip). A2. Periode `status = selesai` → blokir recompute (data sudah final). A3. Data corrupt (misal bobot total > 100) → tolak proses + tampilkan error detail |
| **Pasca-Kondisi** | `nilai_akhir` ter-create/update untuk setiap desa + ranking ter-assign + `audit_trail` mencatat aksi `compute_nilai` dengan payload jumlah desa |

**Idempotency:** trigger ulang aman — `updateOrCreate` dengan UNIQUE `(desa_id, periode_id)` + ranking di-recompute total. Data sebelum/sesudah perubahan boleh disimpan di `audit_trail.payload` untuk traceability.

**Edge case wajib di-test:**
1. Semua desa lengkap → ranking 1..N berurutan tanpa gap.
2. 2 desa nilai akhir sama → ranking sama (tied), desa berikutnya skip nomor (1, 2, 2, 4).
3. Recompute setelah Staff Penilaian edit nilai visitasi → ranking berubah.
4. Periode tanpa desa aktif → return collection kosong, tidak error.

---

### UC-09: Lihat Hasil Penilaian

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Semua role (scope berbeda) |
| **Tujuan** | Tampilkan nilai kuesioner + nilai visitasi + nilai akhir + peringkat |
| **Prasyarat** | Login + `nilai_akhir` periode terkait sudah dihitung |
| **Pemicu** | Pengguna pilih menu "Hasil Penilaian" |
| **Alur Utama** | 1. Buka menu → 2. Sistem tentukan scope berdasarkan role: <br> • Super Admin / Staff Penilaian / Pimpinan: semua desa <br> • Staff Admin Desa: hanya desanya → 3. Tampilkan tabel: nama desa, nilai_kuesioner, nilai_visitasi, nilai_akhir, peringkat → 4. Klik desa untuk detail breakdown per indikator |
| **Alur Alternatif** | A1. Belum ada `nilai_akhir` → tampilkan empty state "Nilai akhir belum dihitung untuk periode ini". A2. Staff Admin Desa akses URL detail desa lain → 403 via `NilaiAkhirPolicy::view` |
| **Pasca-Kondisi** | Pengguna melihat data sesuai scope-nya. Tidak ada perubahan state. |

**Policy `NilaiAkhirPolicy::view`:**
```php
public function view(User $user, NilaiAkhir $nilai): bool
{
    return match ($user->role->slug) {
        'super_admin', 'staff_penilaian', 'pimpinan' => true,
        'staff_admin_desa'                            => $user->desa_id === $nilai->desa_id,
    };
}
```

---

### UC-10: Cetak Laporan

| Elemen | Deskripsi |
|---|---|
| **Aktor** | Semua role (scope = scope view-nya) |
| **Tujuan** | Generate dokumen PDF hasil penilaian untuk dokumentasi/evaluasi |
| **Prasyarat** | Login + `nilai_akhir` periode terkait sudah dihitung |
| **Pemicu** | Pengguna pilih menu "Cetak Laporan" |
| **Alur Utama** | 1. Buka menu → 2. Pilih jenis laporan: <br> • Per Desa (semua role, scope-aware) <br> • Rekapitulasi Periode (semua role, scope-aware) <br> • Audit Trail (Super Admin only) → 3. Pilih periode + filter (jika ada) → 4. Klik "Cetak PDF" → 5. Sistem load data via `LaporanService` → 6. Render Blade `resources/views/laporan/*.blade.php` → 7. DomPDF generate file → 8. Browser download / preview |
| **Alur Alternatif** | A1. Belum ada data hasil → tampilkan pemberitahuan "Data laporan belum tersedia". A2. Staff Admin Desa pilih jenis "Rekapitulasi Periode" → policy izinkan tapi data di-filter hanya desanya. A3. Pimpinan pilih "Audit Trail" → 403 (bukan kewenangannya). A4. DomPDF gagal render (memory limit) → flash error + saran pakai filter lebih spesifik |
| **Pasca-Kondisi** | File PDF terdownload / ter-preview + `audit_trail` mencatat aksi `print` dengan jenis laporan + periode |

**Konvensi penamaan file:**
```
laporan-per-desa-{slug-desa}-{periode}.pdf       → laporan-per-desa-desa-bedugul-2025.pdf
laporan-rekapitulasi-{periode}.pdf               → laporan-rekapitulasi-2025.pdf
laporan-audit-trail-{tanggal-mulai}-{tanggal-selesai}.pdf
```

**Layout PDF (Blade):**
- Header: logo Komisi Informasi Bali + "LAPORAN HASIL PENILAIAN APRESIASI DESA" + periode.
- Info: tanggal cetak, dicetak oleh (nama + role).
- Body: tabel data sesuai jenis laporan.
- Footer: halaman X dari Y + tanda tangan space (untuk laporan formal).
- Format: A4 portrait (rekapitulasi besar boleh landscape).

---

### Cross-Cutting Acceptance Criteria

Berlaku untuk **SEMUA** use case di atas:

1. **Audit Trail** — setiap aksi `create`, `update`, `delete`, `print`, `compute_nilai`, `login`, `logout` WAJIB tercatat di `audit_trail` via Observer atau explicit `AuditTrailService::record()`.
2. **CSRF Protection** — semua form POST/PUT/DELETE pakai `@csrf` Blade directive.
3. **Validation** — semua input lewat FormRequest (bukan inline `$request->validate()`), error messages dalam Bahasa Indonesia formal.
4. **Authorization** — setiap action controller cek policy via `$this->authorize()` ATAU middleware role + policy gate.
5. **Flash Message** — setiap aksi sukses/gagal redirect dengan flash session (`success`, `error`, `warning`) + ditampilkan via Bootstrap alert di layout.
6. **Pagination** — list view selalu paginated (default 15/page) untuk hindari memory exhaustion.
7. **Soft Delete** — `desa`, `users`, `kuesioner` pakai `SoftDeletes` trait; jangan hard delete kecuali Super Admin override.
8. **Locale ID** — semua user-facing string Bahasa Indonesia formal (FormRequest custom messages, validation attributes, alert).

## Proses Utama (Implementation Reference)

Bagian ini berisi **kode contoh + design rationale** untuk setiap proses. Pakai bersamaan dengan Expanded Use Case di atas (UC = "what to test", Proses Utama = "how to build").

### 1. Login (semua role)

Session-based, redirect sesuai role.

```php
// app/Http/Controllers/Auth/LoginController.php
public function authenticate(LoginRequest $request): RedirectResponse
{
    $credentials = $request->validated();

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors(['username' => 'Kredensial tidak valid.'])->onlyInput('username');
    }

    if (! Auth::user()->is_active) {
        Auth::logout();
        return back()->withErrors(['username' => 'Akun Anda telah dinonaktifkan.']);
    }

    AuditTrail::record(Auth::user(), 'login', 'Berhasil login ke sistem');
    $request->session()->regenerate();

    return redirect()->intended(match (Auth::user()->role->slug) {
        'super_admin'        => route('admin.dashboard'),
        'staff_admin_desa'   => route('desa.dashboard'),
        'staff_penilaian'    => route('penilai.dashboard'),
        'pimpinan'           => route('pimpinan.dashboard'),
    });
}
```

### 2. Kelola Data Pengguna (Super Admin)

CRUD `users` + assign `role_id` + (untuk Staff Admin Desa) `desa_id`. Validasi:
- `username` unique (case-insensitive).
- Password minimal 8 karakter, hash via `Hash::make()`.
- Reset password lewat email pakai default Laravel password reset.

### 3. Kelola Data Desa (Super Admin, Staff Admin Desa terbatas desanya)

Super Admin: full CRUD. Staff Admin Desa: hanya `update` desanya sendiri (policy `DesaPolicy::update` cek `$user->desa_id === $desa->id`).

### 4. Kelola Data Kuesioner (Super Admin)

Per periode aktif: tambah/ubah/hapus indikator, set bobot. Validasi total bobot per periode = 100 sebelum periode bisa di-`aktif`-kan.

```php
// app/Http/Requests/StoreKuesionerRequest.php
public function rules(): array
{
    return [
        'periode_id'      => ['required', 'exists:periode_penilaian,id'],
        'kategori'        => ['required', 'string', 'max:100'],
        'kode_indikator'  => [
            'required', 'string', 'max:50',
            Rule::unique('kuesioner')->where(fn ($q) => $q->where('periode_id', $this->periode_id)),
        ],
        'pertanyaan'      => ['required', 'string'],
        'bobot_indikator' => ['required', 'numeric', 'min:0', 'max:100'],
        'urutan'          => ['required', 'integer', 'min:1'],
    ];
}

public function withValidator(Validator $validator): void
{
    $validator->after(function ($v) {
        $totalBobot = Kuesioner::where('periode_id', $this->periode_id)
            ->where('id', '!=', $this->route('kuesioner')?->id ?? 0)
            ->sum('bobot_indikator');

        if ($totalBobot + $this->bobot_indikator > 100) {
            $v->errors()->add('bobot_indikator', 'Total bobot indikator melebihi 100.');
        }
    });
}
```

### 5. Isi Kuesioner (Staff Admin Desa)

Form berisi semua indikator periode aktif. Skor 0–100 per indikator + keterangan opsional. Bisa save draft (tidak final) atau submit (final, tidak bisa diedit lagi tanpa approval Super Admin).

```php
// app/Services/JawabanKuesionerService.php
final class JawabanKuesionerService
{
    public function simpan(Desa $desa, PeriodePenilaian $periode, array $jawaban, User $pengisi): Collection
    {
        return DB::transaction(function () use ($desa, $periode, $jawaban, $pengisi) {
            return collect($jawaban)->map(fn ($item) => JawabanKuesioner::updateOrCreate(
                [
                    'desa_id'       => $desa->id,
                    'kuesioner_id'  => $item['kuesioner_id'],
                    'periode_id'    => $periode->id,
                ],
                [
                    'jawaban'     => $item['jawaban'],
                    'skor'        => $item['skor'],
                    'keterangan'  => $item['keterangan'] ?? null,
                    'diisi_oleh'  => $pengisi->id,
                ]
            ));
        });
    }
}
```

### 6. Kelola Jadwal Visitasi (Super Admin, Staff Penilaian)

Buat jadwal visitasi per desa per periode. Validasi tidak boleh bentrok untuk petugas yang sama.

### 7. Input Penilaian Visitasi (Staff Penilaian)

Setelah `jadwal_visitasi.status = selesai`, input skor per indikator visitasi. Format mirip kuesioner tapi indikatornya beda (observasi lapangan).

### 8. Hitung Nilai Akhir (Super Admin)

Otomatis hitung saat semua kuesioner desa terisi + semua visitasi selesai. Bisa di-trigger ulang (recompute) bila ada koreksi data.

```php
// app/Services/PerhitunganNilaiService.php
final class PerhitunganNilaiService
{
    private const BOBOT_KUESIONER = 0.60;
    private const BOBOT_VISITASI  = 0.40;

    public function hitungSemuaDesa(PeriodePenilaian $periode, User $admin): Collection
    {
        return DB::transaction(function () use ($periode, $admin) {
            $hasilPerDesa = Desa::query()
                ->where('is_active', true)
                ->get()
                ->map(fn (Desa $desa) => $this->hitungSatuDesa($desa, $periode, $admin));

            // Tetapkan peringkat berdasarkan nilai_akhir DESC
            $hasilPerDesa
                ->sortByDesc('nilai_akhir')
                ->values()
                ->each(fn (NilaiAkhir $row, int $idx) => $row->update(['peringkat' => $idx + 1]));

            return $hasilPerDesa;
        });
    }

    public function hitungSatuDesa(Desa $desa, PeriodePenilaian $periode, User $admin): NilaiAkhir
    {
        $nilaiKuesioner = $this->hitungNilaiKuesioner($desa, $periode);
        $nilaiVisitasi  = $this->hitungNilaiVisitasi($desa, $periode);
        $nilaiAkhir     = ($nilaiKuesioner * self::BOBOT_KUESIONER)
                          + ($nilaiVisitasi * self::BOBOT_VISITASI);

        return NilaiAkhir::updateOrCreate(
            ['desa_id' => $desa->id, 'periode_id' => $periode->id],
            [
                'nilai_kuesioner' => round($nilaiKuesioner, 2),
                'nilai_visitasi'  => round($nilaiVisitasi, 2),
                'nilai_akhir'     => round($nilaiAkhir, 2),
                'dihitung_pada'   => now(),
                'dihitung_oleh'   => $admin->id,
            ]
        );
    }

    /**
     * Nilai kuesioner = Σ (skor × bobot_indikator / 100)
     *   skor 0-100, bobot total per periode = 100
     *   hasilnya juga ada di rentang 0-100
     */
    private function hitungNilaiKuesioner(Desa $desa, PeriodePenilaian $periode): float
    {
        return (float) JawabanKuesioner::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->join('kuesioner', 'jawaban_kuesioner.kuesioner_id', '=', 'kuesioner.id')
            ->sum(DB::raw('jawaban_kuesioner.skor * kuesioner.bobot_indikator / 100'));
    }

    /**
     * Nilai visitasi = Σ (skor × bobot / 100)
     *   diinput langsung oleh Staff Penilaian, struktur sama dengan kuesioner.
     */
    private function hitungNilaiVisitasi(Desa $desa, PeriodePenilaian $periode): float
    {
        return (float) PenilaianVisitasi::query()
            ->where('desa_id', $desa->id)
            ->where('periode_id', $periode->id)
            ->sum(DB::raw('skor * bobot / 100'));
    }
}
```

**Business rules kunci:**
- Nilai kuesioner & nilai visitasi sama-sama range 0–100 setelah weighted sum.
- Nilai akhir juga 0–100 karena bobot 0.6 + 0.4 = 1.
- Peringkat ditetapkan setelah semua desa selesai dihitung (sort desc nilai_akhir).
- Recompute mengganti seluruh row `nilai_akhir` periode tersebut → audit trail wajib catat trigger.
- Desa yang kuesionernya **belum lengkap** → `nilai_kuesioner = 0` (atau opsional: skip dari ranking, tergantung kebijakan Owner project).

### 9. Lihat Hasil Penilaian (semua role, scope beda)

| Role | Scope View |
|---|---|
| Super Admin | Semua desa, semua periode, full breakdown |
| Staff Admin Desa | Hanya desanya, periode aktif + histori desanya |
| Staff Penilaian | Semua desa periode aktif, fokus visitasi |
| Pimpinan | Semua desa, ranking + summary, tanpa edit |

### 10. Cetak Laporan (semua role, scope = scope view)

3 jenis laporan PDF:
- **Laporan Hasil Penilaian Per Desa** — detail kuesioner + visitasi + nilai akhir + peringkat.
- **Laporan Rekapitulasi Periode** — tabel semua desa, ranking, statistik.
- **Laporan Audit Trail** (Super Admin only) — log aktivitas pengguna periode tertentu.

```php
// app/Services/LaporanService.php
public function generatePdfRekapitulasi(PeriodePenilaian $periode): \Barryvdh\DomPDF\PDF
{
    $hasil = NilaiAkhir::with('desa')
        ->where('periode_id', $periode->id)
        ->orderBy('peringkat')
        ->get();

    return Pdf::loadView('laporan.rekapitulasi', [
        'periode' => $periode,
        'hasil'   => $hasil,
        'tanggal' => now(),
    ])->setPaper('a4', 'portrait');
}
```

## Backend Conventions (Laravel 13)

### Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   ├── Admin/                     — namespace Super Admin
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php
│   │   │   ├── DesaController.php
│   │   │   ├── KuesionerController.php
│   │   │   ├── PeriodeController.php
│   │   │   └── NilaiAkhirController.php
│   │   ├── Desa/                      — namespace Staff Admin Desa
│   │   │   ├── DashboardController.php
│   │   │   ├── KuesionerController.php
│   │   │   └── HasilController.php
│   │   ├── Penilai/                   — namespace Staff Penilaian
│   │   │   ├── DashboardController.php
│   │   │   ├── JadwalVisitasiController.php
│   │   │   └── PenilaianVisitasiController.php
│   │   ├── Pimpinan/
│   │   │   └── DashboardController.php
│   │   └── LaporanController.php      — shared cetak PDF (scope by role)
│   ├── Requests/                      — FormRequest per endpoint
│   ├── Middleware/
│   │   └── EnsureRole.php             — middleware: ->middleware('role:super_admin')
│   └── ...
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── Desa.php
│   ├── PeriodePenilaian.php
│   ├── Kuesioner.php
│   ├── JawabanKuesioner.php
│   ├── JadwalVisitasi.php
│   ├── PenilaianVisitasi.php
│   ├── NilaiAkhir.php
│   └── AuditTrail.php
├── Policies/
│   ├── DesaPolicy.php
│   ├── KuesionerPolicy.php
│   └── ...
├── Services/
│   ├── PerhitunganNilaiService.php
│   ├── JawabanKuesionerService.php
│   ├── LaporanService.php
│   └── AuditTrailService.php
├── Enums/
│   ├── RoleSlug.php                   — SuperAdmin, StaffAdminDesa, StaffPenilaian, Pimpinan
│   ├── StatusPeriode.php              — Draft, Aktif, Selesai
│   ├── StatusVisitasi.php             — Terjadwal, Berlangsung, Selesai, Dibatalkan
│   └── AksiAudit.php                  — Login, Logout, Create, Update, Delete, Print, ComputeNilai
└── Observers/
    └── AuditTrailObserver.php         — auto-record changes ke audit_trail
```

### Enum Pattern (PHP 8 + PascalCase)

```php
<?php

namespace App\Enums;

enum RoleSlug: string
{
    case SuperAdmin       = 'super_admin';
    case StaffAdminDesa   = 'staff_admin_desa';
    case StaffPenilaian   = 'staff_penilaian';
    case Pimpinan         = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin       => 'Super Admin',
            self::StaffAdminDesa   => 'Staff Admin Desa',
            self::StaffPenilaian   => 'Staff Penilaian Komisi Informasi',
            self::Pimpinan         => 'Pimpinan Komisi Informasi',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::SuperAdmin       => 'admin.dashboard',
            self::StaffAdminDesa   => 'desa.dashboard',
            self::StaffPenilaian   => 'penilai.dashboard',
            self::Pimpinan         => 'pimpinan.dashboard',
        };
    }
}
```

### Routing (web.php)

```php
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Super Admin
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                        [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users',               Admin\UserController::class);
        Route::resource('desa',                Admin\DesaController::class);
        Route::resource('periode',             Admin\PeriodeController::class);
        Route::resource('kuesioner',           Admin\KuesionerController::class);
        Route::post('nilai-akhir/hitung/{periode}', [Admin\NilaiAkhirController::class, 'hitung'])->name('nilai.hitung');
    });

    // Staff Admin Desa
    Route::middleware('role:staff_admin_desa')->prefix('desa')->name('desa.')->group(function () {
        Route::get('/',                  [Desa\DashboardController::class, 'index'])->name('dashboard');
        Route::get('kuesioner',          [Desa\KuesionerController::class, 'edit'])->name('kuesioner.edit');
        Route::put('kuesioner',          [Desa\KuesionerController::class, 'update'])->name('kuesioner.update');
        Route::get('hasil',              [Desa\HasilController::class, 'index'])->name('hasil.index');
    });

    // Staff Penilaian
    Route::middleware('role:staff_penilaian')->prefix('penilai')->name('penilai.')->group(function () {
        Route::get('/',                          [Penilai\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('jadwal-visitasi',       Penilai\JadwalVisitasiController::class);
        Route::resource('penilaian-visitasi',    Penilai\PenilaianVisitasiController::class);
    });

    // Pimpinan
    Route::middleware('role:pimpinan')->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('/',           [Pimpinan\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/peringkat',  [Pimpinan\DashboardController::class, 'peringkat'])->name('peringkat');
    });

    // Laporan (multi-role, scope by policy)
    Route::get('/laporan/{jenis}/{periode}/pdf', [LaporanController::class, 'cetak'])
        ->name('laporan.cetak');
});
```

### Middleware Role

```php
// app/Http/Middleware/EnsureRole.php
final class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$rolesYangDiizinkan): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! in_array(Auth::user()->role->slug, $rolesYangDiizinkan, strict: true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
```

Daftarkan di `bootstrap/app.php` (Laravel 13):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
})
```

### Audit Trail (Observer + Service)

```php
// app/Services/AuditTrailService.php
final class AuditTrailService
{
    public static function record(
        ?User $user,
        AksiAudit $aksi,
        string $deskripsi,
        ?Model $subject = null,
        ?array $payload = null,
    ): AuditTrail {
        return AuditTrail::create([
            'user_id'     => $user?->id,
            'aksi'        => $aksi->value,
            'model_type'  => $subject?->getMorphClass(),
            'model_id'    => $subject?->getKey(),
            'deskripsi'   => $deskripsi,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'payload'     => $payload,
        ]);
    }
}
```

Pasang Observer di model sensitif (Desa, Kuesioner, NilaiAkhir) untuk auto-record `created/updated/deleted`.

### Testing (Pest v4)

```php
// tests/Feature/PerhitunganNilaiTest.php
it('menghitung nilai akhir dengan bobot 60% kuesioner + 40% visitasi', function () {
    $admin   = User::factory()->superAdmin()->create();
    $periode = PeriodePenilaian::factory()->aktif()->create();
    $desa    = Desa::factory()->create();

    // Setup: nilai_kuesioner = 80, nilai_visitasi = 70
    seedKuesionerWithSkor($desa, $periode, totalSkor: 80);
    seedVisitasiWithSkor($desa, $periode, totalSkor: 70);

    actingAs($admin);
    $service = app(PerhitunganNilaiService::class);
    $hasil = $service->hitungSatuDesa($desa, $periode, $admin);

    // (80 * 0.6) + (70 * 0.4) = 48 + 28 = 76
    expect($hasil)
        ->nilai_kuesioner->toEqual(80.00)
        ->nilai_visitasi->toEqual(70.00)
        ->nilai_akhir->toEqual(76.00);
});

it('menetapkan peringkat berdasarkan nilai akhir desc', function () {
    $admin   = User::factory()->superAdmin()->create();
    $periode = PeriodePenilaian::factory()->aktif()->create();
    [$desaA, $desaB, $desaC] = Desa::factory()->count(3)->create();

    seedNilai($desaA, $periode, kues: 90, vis: 80);  // akhir = 86
    seedNilai($desaB, $periode, kues: 70, vis: 60);  // akhir = 66
    seedNilai($desaC, $periode, kues: 80, vis: 70);  // akhir = 76

    app(PerhitunganNilaiService::class)->hitungSemuaDesa($periode, $admin);

    expect(NilaiAkhir::where('desa_id', $desaA->id)->first()->peringkat)->toBe(1);
    expect(NilaiAkhir::where('desa_id', $desaC->id)->first()->peringkat)->toBe(2);
    expect(NilaiAkhir::where('desa_id', $desaB->id)->first()->peringkat)->toBe(3);
});
```

**Verify rules:**
1. Setiap controller HARUS punya minimal feature test happy path + 1 auth failure (403) + 1 role failure.
2. Service `PerhitunganNilaiService` HARUS punya unit test cover formula 60/40 + ranking + edge case (semua nol, kuesioner kosong).
3. Seeder + Factory WAJIB dibuat bersama Model baru (`php artisan make:model Foo -mfs`).
4. Jalankan `vendor/bin/pint --dirty --format agent` sebelum commit.

## Frontend Conventions (Bootstrap 5 + Blade)

### Struktur

```
resources/
├── sass/
│   └── app.scss                       — import bootstrap + bootstrap-icons + variable override
├── js/
│   └── app.js                         — import 'bootstrap' (Popper auto-bundled)
└── views/
    ├── layouts/
    │   ├── app.blade.php              — main layout (navbar + sidebar + content + footer)
    │   ├── auth.blade.php             — login layout (centered card)
    │   └── pdf.blade.php              — PDF layout untuk DomPDF
    ├── components/
    │   ├── alert.blade.php            — Bootstrap alert wrapper
    │   ├── card.blade.php
    │   ├── data-table.blade.php       — table responsive + pagination
    │   └── form/
    │       ├── input.blade.php
    │       ├── select.blade.php
    │       └── textarea.blade.php
    ├── auth/
    │   └── login.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── users/
    │   ├── desa/
    │   ├── kuesioner/
    │   └── nilai-akhir/
    ├── desa/
    ├── penilai/
    ├── pimpinan/
    └── laporan/
        ├── per-desa.blade.php          — view PDF
        └── rekapitulasi.blade.php
```

### Bootstrap 5 Patterns Wajib

- **Layout**: pakai `container` / `container-fluid`, grid `row`/`col-*`, jangan custom flex kecuali perlu.
- **Form**: `form-control`, `form-select`, `form-label`, validasi error pakai `is-invalid` + `<div class="invalid-feedback">`.
- **Table data**: `table table-striped table-hover` + bungkus `table-responsive`.
- **Modal CRUD**: gunakan modal Bootstrap untuk create/edit ringan; halaman tersendiri untuk form kompleks.
- **Notifikasi**: alert Bootstrap (`alert-success`, `alert-danger`) di-flash via session.
- **Icon**: `bootstrap-icons` (`<i class="bi bi-pencil-square"></i>`), **bukan** Font Awesome / Heroicons.
- **Color override**: `$primary` di-set ke `#f53003` (warna brand Komisi Informasi Bali — bisa di-tune saat finalisasi UI).

### Component Blade Reusable

```blade
{{-- resources/views/components/data-table.blade.php --}}
@props(['headers' => [], 'rows' => [], 'aksi' => null])

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
                @if ($aksi)
                    <th class="text-end">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
```

### Anti-Patterns Frontend (NEVER)

- ❌ Pakai class Tailwind apapun (`flex`, `grid-cols-*`, `text-gray-500`, dll.) — project ini Bootstrap.
- ❌ Inline `<style>` di Blade kecuali untuk PDF layout.
- ❌ jQuery — Bootstrap 5 tidak butuh jQuery, gunakan vanilla JS / Alpine.js bila perlu interaktivitas.
- ❌ Mix Bootstrap CSS dengan utility CSS framework lain.
- ❌ Custom CSS di luar `resources/sass/` — tambahkan ke `app.scss` atau partial-nya.

## Sail Workflow (OrbStack)

```bash
# Daily
./vendor/bin/sail up -d                              # bring up stack
./vendor/bin/sail artisan migrate --seed             # migrate + seed
./vendor/bin/sail artisan test --compact             # run pest
./vendor/bin/sail npm run dev                        # vite hot reload
./vendor/bin/sail composer require <package>         # tambah dependency

# Akses
Aplikasi      → http://localhost:8080
MySQL         → localhost:3306 (user sail, pass password, db spk_desa)
Redis         → localhost:6379
Mailpit UI    → http://localhost:8025
```

> Port 8000 dipakai container portainer global di OrbStack — project ini pakai `APP_PORT=8080`. Jangan revert tanpa cek konflik dulu.

## Testing Strategy (Scope Penelitian)

1. **Black Box Testing** — per fitur CRUD + flow penilaian end-to-end. Wajib dokumen di `docs/testing/blackbox-{modul}.md`.
2. **Feature/Unit Test (Pest v4)** — cover service (`PerhitunganNilaiService`, `JawabanKuesionerService`), controller, dan policy.
3. **User Acceptance Testing (UAT)** — Owner project (Komisi Informasi Bali) sign-off per modul.
4. **System Usability Scale (SUS)** opsional — bila skripsi/laporan butuh metrik kuantitatif usability, 10 pertanyaan Likert 5-poin minimal 10 responden lintas role.

## Anti-Patterns Backend (NEVER)

- ❌ Hitung nilai akhir manual di controller — **wajib** lewat `PerhitunganNilaiService`.
- ❌ Bobot kuesioner/visitasi disimpan sebagai magic number di kode — pakai constant di Service / config.
- ❌ Hard-delete `desa` / `users` yang sudah punya relasi nilai_akhir — pakai SoftDeletes + flag `is_active`.
- ❌ Bypass policy/middleware role dengan cek manual di Blade — single source of truth ada di middleware + policy.
- ❌ Simpan password plain text / pakai algoritma selain `bcrypt`.
- ❌ Lewat audit trail untuk aksi kritis (login, hitung nilai, edit kuesioner final, cetak laporan).
- ❌ Bikin Filament Resource (bukan bagian stack project ini).
- ❌ Bikin Livewire Component (bukan bagian stack project ini).
- ❌ Hardcode role string `'super_admin'` di banyak tempat — pakai `RoleSlug` enum.
- ❌ Render Blade di dalam API JSON response (project ini server-rendered, **bukan** API+SPA).

## Bahasa & Dokumentasi

- Kode & identifier: **English** (`KuesionerController`, `hitungNilaiAkhir`, `dihitungOleh`).
- Pesan user-facing: **Bahasa Indonesia formal** (error messages, label form, status, alert, notifikasi).
- Database column: **Indonesia** (`nama_desa`, `tanggal_visitasi`, `nilai_akhir`) — sesuai konvensi domain analisis BAB IV agar konsisten dengan terminologi institusi Komisi Informasi.
- Dokumen akademik (skripsi, laporan kerja praktek): Indonesia formal monokrom, tanpa emoji, tanpa warna dekoratif.
- Commit message: Conventional Commits (English): `feat(kuesioner): tambah validasi total bobot per periode`.

## Quick Reference (Cheat Sheet)

| Aksi | Perintah |
|---|---|
| Bring up stack | `./vendor/bin/sail up -d` |
| Migrate + seed | `./vendor/bin/sail artisan migrate:fresh --seed` |
| Run test | `./vendor/bin/sail artisan test --compact` |
| Vite dev | `./vendor/bin/sail npm run dev` |
| Vite build prod | `./vendor/bin/sail npm run build` |
| Lint PHP | `./vendor/bin/sail bin pint --dirty --format agent` |
| Generate model+migration+factory+seeder | `./vendor/bin/sail artisan make:model Desa -mfs` |
| Generate FormRequest | `./vendor/bin/sail artisan make:request StoreDesaRequest` |
| Tinker | `./vendor/bin/sail artisan tinker` |
| Cek route | `./vendor/bin/sail artisan route:list --except-vendor` |
