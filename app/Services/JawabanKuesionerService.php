<?php

namespace App\Services;

use App\Enums\StatusJawaban;
use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class JawabanKuesionerService
{
    /**
     * Simpan jawaban kuesioner desa untuk periode tertentu.
     *
     * @param  array<int, array{kuesioner_id: int, jawaban?: ?string, skor?: float|int, keterangan?: ?string}>  $jawaban
     */
    public function simpan(
        Desa $desa,
        PeriodePenilaian $periode,
        array $jawaban,
        User $pengisi,
        bool $finalisasi = false,
    ): Collection {
        return DB::transaction(function () use ($desa, $periode, $jawaban, $pengisi, $finalisasi) {
            return collect($jawaban)
                ->filter(fn ($item) => isset($item['kuesioner_id']))
                ->map(fn ($item) => JawabanKuesioner::updateOrCreate(
                    [
                        'desa_id' => $desa->id,
                        'kuesioner_id' => (int) $item['kuesioner_id'],
                        'periode_id' => $periode->id,
                    ],
                    collect([
                        'jawaban' => $item['jawaban'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                        'status' => $finalisasi ? StatusJawaban::Final : StatusJawaban::Draft,
                        'diisi_oleh' => $pengisi->id,
                    ])
                        ->when(
                            array_key_exists('skor', $item),
                            fn ($c) => $c->put('skor', (float) $item['skor'])
                        )
                        ->all()
                ))
                ->values();
        });
    }
}
