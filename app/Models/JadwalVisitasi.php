<?php

namespace App\Models;

use App\Enums\StatusVisitasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalVisitasi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_visitasi';

    protected $fillable = [
        'desa_id',
        'periode_id',
        'tanggal_visitasi',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'petugas_id',
        'status',
        'catatan',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_visitasi' => 'date',
            'status' => StatusVisitasi::class,
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

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(PenilaianVisitasi::class, 'jadwal_id');
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiKuesioner::class, 'jadwal_id');
    }
}
