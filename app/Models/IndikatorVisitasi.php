<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikatorVisitasi extends Model
{
    use HasFactory;

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

    /**
     * Load indikator visitasi aktif untuk desa+periode.
     * Jika desa punya indikator khusus, pakai itu saja. Jika tidak, pakai global (desa_id = null).
     *
     * @return Collection<int, IndikatorVisitasi>
     */
    public static function activeTemplate(int $periodeId, int $desaId): Collection
    {
        $hasSpecific = self::query()
            ->where('periode_id', $periodeId)
            ->where('desa_id', $desaId)
            ->where('is_active', true)
            ->exists();

        return self::query()
            ->where('periode_id', $periodeId)
            ->where(function ($q) use ($desaId, $hasSpecific) {
                if ($hasSpecific) {
                    $q->where('desa_id', $desaId);
                } else {
                    $q->whereNull('desa_id');
                }
            })
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();
    }
}
