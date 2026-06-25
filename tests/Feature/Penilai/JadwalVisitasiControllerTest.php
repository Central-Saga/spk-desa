<?php

use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
    $this->penilai = User::factory()->staffPenilaian()->create();
    $this->periode = PeriodePenilaian::factory()->aktif()->create();
    $this->desa = Desa::factory()->create();
});

// Index
it('Staff Penilaian dapat lihat list jadwal visitasi', function () {
    JadwalVisitasi::factory()->count(3)->create([
        'periode_id' => $this->periode->id,
        'desa_id' => $this->desa->id,
        'petugas_id' => $this->penilai->id,
        'dibuat_oleh' => $this->admin->id,
    ]);

    $this->actingAs($this->penilai)
        ->get('/penilai/jadwal-visitasi')
        ->assertOk();
});

// Store
it('Staff Penilaian dapat tambah jadwal visitasi', function () {
    $this->actingAs($this->penilai)
        ->post('/penilai/jadwal-visitasi', [
            'desa_id' => $this->desa->id,
            'periode_id' => $this->periode->id,
            'tanggal_visitasi' => now()->addDays(7)->toDateString(),
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '12:00',
            'lokasi' => 'Kantor Desa',
            'petugas_id' => $this->penilai->id,
            'status' => 'terjadwal',
        ])
        ->assertRedirect('/penilai/jadwal-visitasi')
        ->assertSessionHas('success');

    expect(JadwalVisitasi::query()->where('desa_id', $this->desa->id)->exists())->toBeTrue();
});

// Update
it('Staff Penilaian dapat update jadwal visitasi', function () {
    $jadwal = JadwalVisitasi::factory()->create([
        'periode_id' => $this->periode->id,
        'desa_id' => $this->desa->id,
        'petugas_id' => $this->penilai->id,
        'dibuat_oleh' => $this->admin->id,
        'status' => 'terjadwal',
    ]);

    $this->actingAs($this->penilai)
        ->put("/penilai/jadwal-visitasi/{$jadwal->id}", [
            'desa_id' => $this->desa->id,
            'periode_id' => $this->periode->id,
            'tanggal_visitasi' => $jadwal->tanggal_visitasi->toDateString(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '13:00',
            'lokasi' => 'Lokasi Updated',
            'petugas_id' => $this->penilai->id,
            'status' => 'berlangsung',
        ])
        ->assertRedirect('/penilai/jadwal-visitasi')
        ->assertSessionHas('success');

    expect($jadwal->fresh()->status->value)->toBe('berlangsung');
});

// Destroy
it('Staff Penilaian dapat hapus jadwal tanpa penilaian', function () {
    $jadwal = JadwalVisitasi::factory()->create([
        'periode_id' => $this->periode->id,
        'desa_id' => $this->desa->id,
        'petugas_id' => $this->penilai->id,
        'dibuat_oleh' => $this->admin->id,
    ]);

    $this->actingAs($this->penilai)
        ->delete("/penilai/jadwal-visitasi/{$jadwal->id}")
        ->assertRedirect('/penilai/jadwal-visitasi')
        ->assertSessionHas('success');
});

it('blokir hapus jadwal yang sudah punya penilaian', function () {
    $jadwal = JadwalVisitasi::factory()->create([
        'periode_id' => $this->periode->id,
        'desa_id' => $this->desa->id,
        'petugas_id' => $this->penilai->id,
        'dibuat_oleh' => $this->admin->id,
    ]);

    PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'periode_id' => $this->periode->id,
        'desa_id' => $this->desa->id,
        'dinilai_oleh' => $this->penilai->id,
    ]);

    $this->actingAs($this->penilai)
        ->delete("/penilai/jadwal-visitasi/{$jadwal->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
});
