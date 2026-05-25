<?php

namespace Database\Factories;

use App\Models\JadwalVisitasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalVisitasi>
 */
class JadwalVisitasiFactory extends Factory
{
    protected $model = JadwalVisitasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'desa_id' => null,
            'periode_id' => null,
            'tanggal_visitasi' => fake()->date(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '12:00',
            'lokasi' => 'Kantor '.fake()->word(),
            'petugas_id' => null,
            'status' => 'terjadwal',
            'catatan' => fake()->optional()->sentence(),
            'dibuat_oleh' => null,
        ];
    }
}
