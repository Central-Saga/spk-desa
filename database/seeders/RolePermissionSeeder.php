<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        // Pengguna
        'pengguna.view', 'pengguna.create', 'pengguna.update', 'pengguna.delete',

        // Desa
        'desa.view', 'desa.view-own', 'desa.create', 'desa.update', 'desa.update-own', 'desa.delete',

        // Periode
        'periode.view', 'periode.create', 'periode.update', 'periode.delete',

        // Kuesioner
        'kuesioner.view', 'kuesioner.create', 'kuesioner.update', 'kuesioner.delete',
        'kuesioner.isi', 'kuesioner.submit',

        // Visitasi
        'jadwal-visitasi.view', 'jadwal-visitasi.create', 'jadwal-visitasi.update', 'jadwal-visitasi.delete',
        'penilaian-visitasi.view', 'penilaian-visitasi.create', 'penilaian-visitasi.update',

        // Nilai akhir
        'nilai-akhir.view', 'nilai-akhir.compute',

        // Hasil
        'hasil.view-all', 'hasil.view-own',

        // Laporan
        'laporan.cetak', 'laporan.cetak-audit',

        // Audit Trail
        'audit-trail.view',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $superAdmin = Role::findOrCreate(RoleSlug::SuperAdmin->value, 'web');
        $superAdmin->syncPermissions(Permission::all());

        $staffAdminDesa = Role::findOrCreate(RoleSlug::StaffAdminDesa->value, 'web');
        $staffAdminDesa->syncPermissions([
            'desa.view-own', 'desa.update-own',
            'kuesioner.view', 'kuesioner.isi', 'kuesioner.submit',
            'hasil.view-own',
            'laporan.cetak',
        ]);

        $staffPenilaian = Role::findOrCreate(RoleSlug::StaffPenilaian->value, 'web');
        $staffPenilaian->syncPermissions([
            'desa.view',
            'jadwal-visitasi.view', 'jadwal-visitasi.create', 'jadwal-visitasi.update', 'jadwal-visitasi.delete',
            'penilaian-visitasi.view', 'penilaian-visitasi.create', 'penilaian-visitasi.update',
            'hasil.view-all',
            'laporan.cetak',
        ]);

        $pimpinan = Role::findOrCreate(RoleSlug::Pimpinan->value, 'web');
        $pimpinan->syncPermissions([
            'desa.view',
            'hasil.view-all',
            'laporan.cetak',
        ]);
    }
}
