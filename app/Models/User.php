<?php

namespace App\Models;

use App\Enums\RoleSlug;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'is_active', 'desa_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function jadwalDibuat(): HasMany
    {
        return $this->hasMany(JadwalVisitasi::class, 'dibuat_oleh');
    }

    public function jadwalSebagaiPetugas(): HasMany
    {
        return $this->hasMany(JadwalVisitasi::class, 'petugas_id');
    }

    public function penilaianVisitasi(): HasMany
    {
        return $this->hasMany(PenilaianVisitasi::class, 'dinilai_oleh');
    }

    public function jawabanKuesioner(): HasMany
    {
        return $this->hasMany(JawabanKuesioner::class, 'diisi_oleh');
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleSlug::SuperAdmin->value);
    }

    public function isStaffAdminDesa(): bool
    {
        return $this->hasRole(RoleSlug::StaffAdminDesa->value);
    }

    public function isStaffPenilaian(): bool
    {
        return $this->hasRole(RoleSlug::StaffPenilaian->value);
    }

    public function isPimpinan(): bool
    {
        return $this->hasRole(RoleSlug::Pimpinan->value);
    }

    public function primaryRoleSlug(): ?RoleSlug
    {
        $slug = $this->getRoleNames()->first();

        return $slug ? RoleSlug::tryFrom($slug) : null;
    }
}
