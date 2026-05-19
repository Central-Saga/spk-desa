<?php

use App\Enums\RoleSlug;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Desa;
use App\Http\Controllers\HasilPenilaianController;
use App\Http\Controllers\Penilai;
use App\Http\Controllers\Pimpinan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = Auth::user();
        $slug = $user->primaryRoleSlug();

        return $slug
            ? redirect()->route($slug->dashboardRoute())
            : abort(403, 'Role pengguna belum diatur.');
    })->name('dashboard');

    // Hasil penilaian — multi-role, scope-aware
    Route::get('/hasil-penilaian', [HasilPenilaianController::class, 'index'])
        ->name('hasil.index');
    Route::get('/hasil-penilaian/{nilai}', [HasilPenilaianController::class, 'show'])
        ->name('hasil.show');

    // Super Admin
    Route::middleware('role:'.RoleSlug::SuperAdmin->value)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

            Route::resource('pengguna', Admin\PenggunaController::class)
                ->except(['show'])
                ->parameters(['pengguna' => 'pengguna']);

            Route::resource('desa', Admin\DesaController::class)->except(['show']);

            Route::resource('periode', Admin\PeriodeController::class)
                ->except(['show'])
                ->parameters(['periode' => 'periode']);

            Route::resource('kuesioner', Admin\KuesionerController::class)
                ->except(['show'])
                ->parameters(['kuesioner' => 'kuesioner']);

            Route::get('nilai-akhir', [Admin\NilaiAkhirController::class, 'index'])->name('nilai-akhir.index');
            Route::post('nilai-akhir/{periode}/hitung', [Admin\NilaiAkhirController::class, 'hitung'])->name('nilai-akhir.hitung');
        });

    // Staff Admin Desa
    Route::middleware('role:'.RoleSlug::StaffAdminDesa->value)
        ->prefix('desa')
        ->name('desa.')
        ->group(function () {
            Route::get('/', [Desa\DashboardController::class, 'index'])->name('dashboard');
            Route::get('profil', [Desa\ProfilDesaController::class, 'edit'])->name('profil.edit');
            Route::put('profil', [Desa\ProfilDesaController::class, 'update'])->name('profil.update');
            Route::get('kuesioner', [Desa\KuesionerController::class, 'edit'])->name('kuesioner.edit');
            Route::put('kuesioner', [Desa\KuesionerController::class, 'update'])->name('kuesioner.update');
        });

    // Staff Penilaian
    Route::middleware('role:'.RoleSlug::StaffPenilaian->value.','.RoleSlug::SuperAdmin->value)
        ->prefix('penilai')
        ->name('penilai.')
        ->group(function () {
            Route::get('/', [Penilai\DashboardController::class, 'index'])->name('dashboard');
            Route::resource('jadwal-visitasi', Penilai\JadwalVisitasiController::class)
                ->except(['show'])
                ->parameters(['jadwal-visitasi' => 'jadwalVisitasi']);

            Route::get('penilaian-visitasi', [Penilai\PenilaianVisitasiController::class, 'index'])
                ->name('penilaian-visitasi.index');
            Route::get('penilaian-visitasi/{jadwalVisitasi}', [Penilai\PenilaianVisitasiController::class, 'edit'])
                ->name('penilaian-visitasi.edit');
            Route::put('penilaian-visitasi/{jadwalVisitasi}', [Penilai\PenilaianVisitasiController::class, 'update'])
                ->name('penilaian-visitasi.update');
        });

    // Pimpinan
    Route::middleware('role:'.RoleSlug::Pimpinan->value)
        ->prefix('pimpinan')
        ->name('pimpinan.')
        ->group(function () {
            Route::get('/', [Pimpinan\DashboardController::class, 'index'])->name('dashboard');
        });
});
