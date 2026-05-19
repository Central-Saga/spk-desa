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

## Proses Utama (10 Proses)

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
