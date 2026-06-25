<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikatorVisitasi extends Model
{
    protected $table = 'indikator_visitasi';

    protected $fillable = [
        'periode_id',
        'desa_id',
        'kategori',
        'kode',
        'indikator_visitasi',
        'deskripsi',
        'bobot',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }
}
