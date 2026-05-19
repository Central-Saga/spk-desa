<?php

use App\Enums\RoleSlug;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Desa;
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

    // Super Admin
    Route::middleware('role:'.RoleSlug::SuperAdmin->value)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
        });

    // Staff Admin Desa
    Route::middleware('role:'.RoleSlug::StaffAdminDesa->value)
        ->prefix('desa')
        ->name('desa.')
        ->group(function () {
            Route::get('/', [Desa\DashboardController::class, 'index'])->name('dashboard');
        });

    // Staff Penilaian
    Route::middleware('role:'.RoleSlug::StaffPenilaian->value)
        ->prefix('penilai')
        ->name('penilai.')
        ->group(function () {
            Route::get('/', [Penilai\DashboardController::class, 'index'])->name('dashboard');
        });

    // Pimpinan
    Route::middleware('role:'.RoleSlug::Pimpinan->value)
        ->prefix('pimpinan')
        ->name('pimpinan.')
        ->group(function () {
            Route::get('/', [Pimpinan\DashboardController::class, 'index'])->name('dashboard');
        });
});
