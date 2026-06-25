<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiVisitasiGambar extends Model
{
    use HasFactory;

    protected $table = 'bukti_visitasi_gambar';

    protected $fillable = ['penilaian_visitasi_id', 'path', 'urutan'];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function penilaianVisitasi(): BelongsTo
    {
        return $this->belongsTo(PenilaianVisitasi::class, 'penilaian_visitasi_id');
    }
}
