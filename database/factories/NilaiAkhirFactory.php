<?php

namespace Database\Factories;

use App\Models\NilaiAkhir;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NilaiAkhir>
 */
class NilaiAkhirFactory extends Factory
{
    protected $model = NilaiAkhir::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desa_id' => null,
            'periode_id' => null,
            'nilai_kuesioner' => fake()->numberBetween(0, 100),
            'nilai_visitasi' => fake()->numberBetween(0, 100),
            'nilai_akhir' => fake()->numberBetween(0, 100),
            'peringkat' => null,
            'dihitung_pada' => now(),
            'dihitung_oleh' => null,
        ];
    }
}
