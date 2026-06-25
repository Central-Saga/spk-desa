<?php

namespace App\Policies;

use App\Enums\RoleSlug;
use App\Models\Desa;
use App\Models\User;

class DesaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('desa.view') || $user->can('desa.view-own');
    }

    public function view(User $user, Desa $desa): bool
    {
        return match ($user->primaryRoleSlug()) {
            RoleSlug::SuperAdmin, RoleSlug::StaffPenilaian, RoleSlug::Pimpinan => true,
            RoleSlug::StaffAdminDesa => $user->desa_id === $desa->id,
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->can('desa.create');
    }

    public function update(User $user, Desa $desa): bool
    {
        return match ($user->primaryRoleSlug()) {
            RoleSlug::SuperAdmin => $user->can('desa.update'),
            RoleSlug::StaffAdminDesa => $user->can('desa.update-own') && $user->desa_id === $desa->id,
            default => false,
        };
    }

    public function delete(User $user, Desa $desa): bool
    {
        return $user->can('desa.delete');
    }

    public function restore(User $user, Desa $desa): bool
    {
        return $user->can('desa.delete');
    }

    public function forceDelete(User $user, Desa $desa): bool
    {
        return $user->can('desa.delete');
    }
}
