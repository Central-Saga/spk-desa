<?php

namespace App\Models;

use App\Enums\StatusJawaban;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanKuesioner extends Model
{
    use HasFactory;

    protected $table = 'jawaban_kuesioner';

    protected $fillable = [
        'desa_id',
        'kuesioner_id',
        'periode_id',
        'jawaban',
        'skor',
        'keterangan',
        'status',
        'diisi_oleh',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'decimal:2',
            'status' => StatusJawaban::class,
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function kuesioner(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    public function pengisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diisi_oleh');
    }
}
