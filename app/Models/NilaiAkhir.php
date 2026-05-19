<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiAkhir extends Model
{
    use HasFactory;

    protected $table = 'nilai_akhir';

    protected $fillable = [
        'desa_id',
        'periode_id',
        'nilai_kuesioner',
        'nilai_visitasi',
        'nilai_akhir',
        'peringkat',
        'dihitung_pada',
        'dihitung_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nilai_kuesioner' => 'decimal:2',
            'nilai_visitasi' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
            'peringkat' => 'integer',
            'dihitung_pada' => 'datetime',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function penghitung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihitung_oleh');
    }
}
