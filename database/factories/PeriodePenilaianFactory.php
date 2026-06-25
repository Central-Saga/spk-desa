<?php

namespace Database\Factories;

use App\Enums\StatusPeriode;
use App\Models\PeriodePenilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodePenilaian>
 */
class PeriodePenilaianFactory extends Factory
{
    protected $model = PeriodePenilaian::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2027);

        return [
            'tahun' => $year,
            'nama' => 'Penilaian Apresiasi Desa '.$year,
            'tanggal_mulai' => "{$year}-06-01",
            'tanggal_selesai' => "{$year}-12-31",
            'status' => StatusPeriode::Draft->value,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn () => ['status' => StatusPeriode::Aktif->value]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => ['status' => StatusPeriode::Selesai->value]);
    }
}
