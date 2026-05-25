<?php

namespace Database\Factories;

use App\Models\JawabanKuesioner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JawabanKuesioner>
 */
class JawabanKuesionerFactory extends Factory
{
    protected $model = JawabanKuesioner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desa_id' => null,
            'kuesioner_id' => null,
            'periode_id' => null,
            'jawaban' => fake()->paragraph(),
            'skor' => fake()->numberBetween(0, 100),
            'keterangan' => fake()->optional()->sentence(),
            'status' => 'draft',
            'diisi_oleh' => null,
        ];
    }

    public function final(): static
    {
        return $this->state(fn () => ['status' => 'final']);
    }
}
