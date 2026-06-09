<?php

namespace App\Models;

use App\Enums\StatusPeriode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodePenilaian extends Model
{
    use HasFactory;

    protected $table = 'periode_penilaian';

    protected $fillable = [
        'tahun',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status' => StatusPeriode::class,
        ];
    }

    public function kuesioner(): HasMany
    {
        return $this->hasMany(Kuesioner::class, 'periode_id');
    }

    public function jawabanKuesioner(): HasMany
    {
        return $this->hasMany(JawabanKuesioner::class, 'periode_id');
    }

    public function jadwalVisitasi(): HasMany
    {
        return $this->hasMany(JadwalVisitasi::class, 'periode_id');
    }

    public function indikatorVisitasi(): HasMany
    {
        return $this->hasMany(IndikatorVisitasi::class, 'periode_id');
    }

    public function penilaianVisitasi(): HasMany
    {
        return $this->hasMany(PenilaianVisitasi::class, 'periode_id');
    }

    public function nilaiAkhir(): HasMany
    {
        return $this->hasMany(NilaiAkhir::class, 'periode_id');
    }
}
