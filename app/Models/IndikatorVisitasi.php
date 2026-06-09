<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    /**
     * Batasi query untuk satu desa dalam satu periode.
     */
    public function scopeForPeriodeDesa(Builder $query, int $periodeId, ?int $desaId): Builder
    {
        return $query
            ->where('periode_id', $periodeId)
            ->when($desaId, fn (Builder $q) => $q->where('desa_id', $desaId));
    }

    /**
     * Bangun prefix kode indikator dari nama desa, contoh: "Desa Bebandem" => "VIS-BEBANDEM".
     */
    public static function kodePrefixUntukDesa(?Desa $desa): string
    {
        if (! $desa) {
            return 'VIS-UMUM';
        }

        $bagian = Str::of($desa->nama)
            ->replaceMatches('/^desa\s+/i', '')
            ->slug('')
            ->upper()
            ->value();

        return 'VIS-'.($bagian !== '' ? $bagian : 'DESA'.$desa->id);
    }

    /**
     * Hasilkan kode indikator berikutnya yang belum dipakai untuk periode + desa.
     */
    public static function generateKode(int $periodeId, ?Desa $desa): string
    {
        $prefix = self::kodePrefixUntukDesa($desa);

        $urutan = self::query()
            ->where('periode_id', $periodeId)
            ->where('desa_id', $desa?->id)
            ->where('kode', 'like', $prefix.'-%')
            ->count() + 1;

        do {
            $kode = $prefix.'-'.str_pad((string) $urutan, 2, '0', STR_PAD_LEFT);
            $urutan++;
        } while (self::query()
            ->where('periode_id', $periodeId)
            ->where('desa_id', $desa?->id)
            ->where('kode', $kode)
            ->exists());

        return $kode;
    }
}
