<?php

namespace Database\Factories;

use App\Models\PenilaianVisitasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PenilaianVisitasi>
 */
class PenilaianVisitasiFactory extends Factory
{
    protected $model = PenilaianVisitasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jadwal_id' => null,
            'desa_id' => null,
            'periode_id' => null,
            'indikator_visitasi' => 'Indikator '.fake()->word(),
            'skor' => fake()->numberBetween(0, 100),
            'bobot' => 25,
            'keterangan' => fake()->optional()->sentence(),
            'bukti_gambar' => null,
            'dinilai_oleh' => null,
            'tanggal_input' => now(),
        ];
    }
}
