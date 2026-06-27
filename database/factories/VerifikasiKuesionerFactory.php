<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Models\VerifikasiKuesioner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerifikasiKuesioner>
 */
class VerifikasiKuesionerFactory extends Factory
{
    protected $model = VerifikasiKuesioner::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desa_id' => Desa::factory(),
            'periode_id' => PeriodePenilaian::factory(),
            'kuesioner_id' => Kuesioner::factory(),
            'status_verifikasi' => fake()->randomElement(['disetujui', 'ditolak', 'perlu_perbaikan']),
            'catatan' => fake()->optional()->sentence(),
            'diverifikasi_oleh' => User::factory(),
            'tanggal_verifikasi' => now(),
        ];
    }
}
