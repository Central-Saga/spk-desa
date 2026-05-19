<?php

namespace App\Enums;

enum RoleSlug: string
{
    case SuperAdmin = 'super_admin';
    case StaffAdminDesa = 'staff_admin_desa';
    case StaffPenilaian = 'staff_penilaian';
    case Pimpinan = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::StaffAdminDesa => 'Staff Admin Desa',
            self::StaffPenilaian => 'Staff Penilaian Komisi Informasi',
            self::Pimpinan => 'Pimpinan Komisi Informasi',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'admin.dashboard',
            self::StaffAdminDesa => 'desa.dashboard',
            self::StaffPenilaian => 'penilai.dashboard',
            self::Pimpinan => 'pimpinan.dashboard',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
