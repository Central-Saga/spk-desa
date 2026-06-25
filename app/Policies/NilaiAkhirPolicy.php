<?php

namespace App\Policies;

use App\Enums\RoleSlug;
use App\Models\NilaiAkhir;
use App\Models\User;

class NilaiAkhirPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('nilai-akhir.view') || $user->can('hasil.view-all') || $user->can('hasil.view-own');
    }

    public function view(User $user, NilaiAkhir $nilai): bool
    {
        return match ($user->primaryRoleSlug()) {
            RoleSlug::SuperAdmin, RoleSlug::StaffPenilaian, RoleSlug::Pimpinan => true,
            RoleSlug::StaffAdminDesa => $user->desa_id === $nilai->desa_id,
            default => false,
        };
    }

    public function compute(User $user): bool
    {
        return $user->can('nilai-akhir.compute');
    }
}
