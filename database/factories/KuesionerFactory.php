<?php

namespace Database\Factories;

use App\Models\Kuesioner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kuesioner>
 */
class KuesionerFactory extends Factory
{
    protected $model = Kuesioner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periode_id' => null,
            'kategori' => fake()->randomElement(['Transparansi', 'Partisipasi', 'Pelayanan']),
            'kode_indikator' => 'IND-'.fake()->unique()->numberBetween(1000, 9999),
            'pertanyaan' => fake()->sentence(),
            'bobot_indikator' => 25,
            'urutan' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
