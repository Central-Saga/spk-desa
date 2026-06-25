<?php

use App\Enums\StatusVisitasi;
use App\Models\BuktiVisitasiGambar;
use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

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
                    'bukti_gambar' => [$gambar],
                ],
            ],
        ])
        ->assertRedirect("/penilai/penilaian-visitasi/{$jadwal->id}")
        ->assertSessionHas('success');

    $penilaian = PenilaianVisitasi::query()->firstOrFail();

    expect($penilaian->buktiGambar)->toHaveCount(1)
        ->and($jadwal->fresh()->status)->toBe(StatusVisitasi::Selesai)
        ->and(Storage::disk('public')->exists($penilaian->buktiGambar->first()->path))->toBeTrue();
});

it('menyimpan multiple bukti gambar per indikator', function () {
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

    $gambar1 = UploadedFile::fake()->image('bukti-1.jpg');
    $gambar2 = UploadedFile::fake()->image('bukti-2.jpg');

    $this->actingAs($penilai)
        ->put("/penilai/penilaian-visitasi/{$jadwal->id}", [
            'penilaian' => [[
                'indikator' => 'Kondisi Fisik Kantor Desa',
                'bobot' => 20,
                'skor' => 85,
                'bukti_gambar' => [$gambar1, $gambar2],
            ]],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $penilaian = PenilaianVisitasi::query()->firstOrFail();

    expect($penilaian->buktiGambar)->toHaveCount(2)
        ->and($penilaian->buktiGambar->pluck('urutan')->all())->toBe([1, 2]);

    foreach ($penilaian->buktiGambar as $g) {
        expect(Storage::disk('public')->exists($g->path))->toBeTrue();
    }
});

it('dapat hapus salah satu bukti gambar dan tambah gambar baru', function () {
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
        'status' => StatusVisitasi::Selesai,
    ]);

    $penilaian = PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'desa_id' => $desa->id,
        'periode_id' => $periode->id,
        'indikator_visitasi' => 'Indikator A',
        'dinilai_oleh' => $penilai->id,
    ]);

    $g1 = BuktiVisitasiGambar::factory()->create([
        'penilaian_visitasi_id' => $penilaian->id,
        'path' => 'bukti-visitasi/'.$jadwal->id.'/old1.jpg',
        'urutan' => 1,
    ]);
    $g2 = BuktiVisitasiGambar::factory()->create([
        'penilaian_visitasi_id' => $penilaian->id,
        'path' => 'bukti-visitasi/'.$jadwal->id.'/old2.jpg',
        'urutan' => 2,
    ]);

    Storage::disk('public')->put($g1->path, 'fake1');
    Storage::disk('public')->put($g2->path, 'fake2');

    $gambarBaru = UploadedFile::fake()->image('bukti-new.jpg');

    $this->actingAs($penilai)
        ->put("/penilai/penilaian-visitasi/{$jadwal->id}", [
            'penilaian' => [[
                'indikator' => 'Indikator A',
                'bobot' => 20,
                'skor' => 90,
                'bukti_gambar' => [$gambarBaru],
                'hapus_gambar' => [$g1->id],
            ]],
        ])
        ->assertSessionHas('success');

    $penilaian->refresh();

    expect($penilaian->buktiGambar)->toHaveCount(2)
        ->and(BuktiVisitasiGambar::find($g1->id))->toBeNull()
        ->and(Storage::disk('public')->exists($g1->path))->toBeFalse();
});

it('tidak bisa hapus gambar milik penilaian lain', function () {
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
    ]);

    $penilaianA = PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'desa_id' => $desa->id,
        'periode_id' => $periode->id,
        'indikator_visitasi' => 'A',
        'dinilai_oleh' => $penilai->id,
    ]);

    $penilaianB = PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'desa_id' => $desa->id,
        'periode_id' => $periode->id,
        'indikator_visitasi' => 'B',
        'dinilai_oleh' => $penilai->id,
    ]);

    $gB = BuktiVisitasiGambar::factory()->create([
        'penilaian_visitasi_id' => $penilaianB->id,
    ]);

    $this->actingAs($penilai)
        ->put("/penilai/penilaian-visitasi/{$jadwal->id}", [
            'penilaian' => [[
                'indikator' => 'A',
                'bobot' => 20,
                'skor' => 80,
                'hapus_gambar' => [$gB->id],
            ]],
        ]);

    expect(BuktiVisitasiGambar::find($gB->id))->not->toBeNull();
});
