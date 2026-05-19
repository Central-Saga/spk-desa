<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Desa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'desa';

    protected $fillable = [
        'nama',
        'alamat',
        'kecamatan',
        'kabupaten',
        'kode_pos',
        'telepon',
        'email',
        'kepala_desa',
        'jumlah_penduduk',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'jumlah_penduduk' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function jawabanKuesioner(): HasMany
    {
        return $this->hasMany(JawabanKuesioner::class);
    }

    public function jadwalVisitasi(): HasMany
    {
        return $this->hasMany(JadwalVisitasi::class);
    }

    public function penilaianVisitasi(): HasMany
    {
        return $this->hasMany(PenilaianVisitasi::class);
    }

    public function nilaiAkhir(): HasMany
    {
        return $this->hasMany(NilaiAkhir::class);
    }
}
