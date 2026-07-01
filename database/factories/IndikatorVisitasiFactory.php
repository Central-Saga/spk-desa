<?php

namespace Database\Factories;

use App\Models\IndikatorVisitasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndikatorVisitasi>
 */
class IndikatorVisitasiFactory extends Factory
{
    protected $model = IndikatorVisitasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'periode_id' => null,
            'desa_id' => null,
            'kategori' => fake()->randomElement(['Observasi', 'Wawancara', 'Dokumentasi']),
            'kode' => 'V-'.fake()->unique()->numberBetween(100, 999),
            'indikator_visitasi' => fake()->sentence(),
            'deskripsi' => fake()->sentence(),
            'bobot' => 20,
            'urutan' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
