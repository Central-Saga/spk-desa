<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianVisitasi extends Model
{
    use HasFactory;

    protected $table = 'penilaian_visitasi';

    protected $fillable = [
        'jadwal_id',
        'desa_id',
        'periode_id',
        'indikator_visitasi',
        'skor',
        'bobot',
        'keterangan',
        'dinilai_oleh',
        'tanggal_input',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'decimal:2',
            'bobot' => 'decimal:2',
            'tanggal_input' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalVisitasi::class, 'jadwal_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
