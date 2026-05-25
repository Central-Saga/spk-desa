<?php

namespace Database\Factories;

use App\Models\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Desa>
 */
class DesaFactory extends Factory
{
    protected $model = Desa::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Desa '.fake()->unique()->city(),
            'alamat' => fake()->streetAddress(),
            'kecamatan' => fake()->word(),
            'kabupaten' => fake()->randomElement(['Tabanan', 'Bangli', 'Gianyar', 'Karangasem']),
            'kepala_desa' => fake()->name(),
            'jumlah_penduduk' => fake()->numberBetween(500, 10000),
            'is_active' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
