<?php

namespace App\Services;

use App\Enums\AksiAudit;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class AuditTrailService
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public static function record(
        ?User $user,
        AksiAudit $aksi,
        string $deskripsi,
        ?Model $subject = null,
        ?array $payload = null,
    ): AuditTrail {
        return AuditTrail::create([
            'user_id' => $user?->id,
            'aksi' => $aksi->value,
            'model_type' => $subject?->getMorphClass(),
            'model_id' => $subject?->getKey(),
            'deskripsi' => $deskripsi,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
