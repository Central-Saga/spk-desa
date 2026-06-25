<?php

namespace Database\Factories;

use App\Models\BuktiVisitasiGambar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BuktiVisitasiGambar>
 */
class BuktiVisitasiGambarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'penilaian_visitasi_id' => null,
            'path' => 'bukti-visitasi/1/'.fake()->word().'.jpg',
            'urutan' => 1,
        ];
    }
}
