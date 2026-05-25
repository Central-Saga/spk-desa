<?php

namespace App\Policies;

use App\Models\Kuesioner;
use App\Models\User;

class KuesionerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('kuesioner.view') || $user->can('kuesioner.isi');
    }

    public function view(User $user, Kuesioner $kuesioner): bool
    {
        return $user->can('kuesioner.view') || $user->can('kuesioner.isi');
    }

    public function create(User $user): bool
    {
        return $user->can('kuesioner.create');
    }

    public function update(User $user, Kuesioner $kuesioner): bool
    {
        if (! $user->can('kuesioner.update')) {
            return false;
        }

        // Periode aktif yang sudah punya jawaban tidak boleh diubah
        if ($kuesioner->periode->status->value === 'aktif' && $kuesioner->jawaban()->exists()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Kuesioner $kuesioner): bool
    {
        return $user->can('kuesioner.delete');
    }

    public function isi(User $user): bool
    {
        return $user->can('kuesioner.isi') && $user->desa_id !== null;
    }

    public function submit(User $user): bool
    {
        return $user->can('kuesioner.submit') && $user->desa_id !== null;
    }
}
