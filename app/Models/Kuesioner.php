<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kuesioner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kuesioner';

    protected $fillable = [
        'periode_id',
        'kategori',
        'kode_indikator',
        'pertanyaan',
        'bobot_indikator',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bobot_indikator' => 'decimal:2',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanKuesioner::class);
    }
}
