<?php

namespace App\Models;

use App\Enums\AksiAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditTrail extends Model
{
    public $timestamps = false;

    protected $table = 'audit_trail';

    protected $fillable = [
        'user_id',
        'aksi',
        'model_type',
        'model_id',
        'deskripsi',
        'ip_address',
        'user_agent',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'aksi' => AksiAudit::class,
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
