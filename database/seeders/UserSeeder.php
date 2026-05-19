<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@spk-desa.test',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles([RoleSlug::SuperAdmin->value]);

        $desa = Desa::query()->orderBy('id')->first();

        $staffDesa = User::firstOrCreate(
            ['username' => 'staffdesa'],
            [
                'name' => 'Staff Admin Desa Bedugul',
                'email' => 'staffdesa@spk-desa.test',
                'password' => Hash::make('password'),
                'is_active' => true,
                'desa_id' => $desa?->id,
            ]
        );
        $staffDesa->syncRoles([RoleSlug::StaffAdminDesa->value]);

        $staffPenilai = User::firstOrCreate(
            ['username' => 'penilai'],
            [
                'name' => 'Staff Penilaian Komisi Informasi',
                'email' => 'penilai@spk-desa.test',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $staffPenilai->syncRoles([RoleSlug::StaffPenilaian->value]);

        $pimpinan = User::firstOrCreate(
            ['username' => 'pimpinan'],
            [
                'name' => 'Pimpinan Komisi Informasi Bali',
                'email' => 'pimpinan@spk-desa.test',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $pimpinan->syncRoles([RoleSlug::Pimpinan->value]);
    }
}
