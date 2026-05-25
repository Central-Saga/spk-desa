<?php

use App\Enums\StatusVisitasi;
use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('menyimpan penilaian visitasi dengan bukti gambar', function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');

    $admin = User::factory()->superAdmin()->create();
    $penilai = User::factory()->staffPenilaian()->create();
    $periode = PeriodePenilaian::factory()->aktif()->create();
    $desa = Desa::factory()->create();

    $jadwal = JadwalVisitasi::factory()->create([
        'periode_id' => $periode->id,
        'desa_id' => $desa->id,
        'petugas_id' => $penilai->id,
        'dibuat_oleh' => $admin->id,
        'status' => StatusVisitasi::Terjadwal,
    ]);

    $gambar = UploadedFile::fake()->image('bukti-visitasi.jpg');

    $this->actingAs($penilai)
        ->put("/penilai/penilaian-visitasi/{$jadwal->id}", [
            'penilaian' => [
                [
                    'indikator' => 'Kondisi Fisik Kantor Desa',
                    'bobot' => 20,
                    'skor' => 85,
                    'keterangan' => 'Kondisi kantor desa baik.',
                    'bukti_gambar' => $gambar,
                ],
            ],
        ])
        ->assertRedirect("/penilai/penilaian-visitasi/{$jadwal->id}")
        ->assertSessionHas('success');

    $penilaian = PenilaianVisitasi::query()->firstOrFail();

    expect($penilaian->bukti_gambar)->not->toBeNull()
        ->and($jadwal->fresh()->status)->toBe(StatusVisitasi::Selesai)
        ->and(Storage::disk('public')->exists($penilaian->bukti_gambar))->toBeTrue();
});
