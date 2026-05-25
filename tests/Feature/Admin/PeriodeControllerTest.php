<?php

use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
});

// Index
it('Super Admin dapat lihat list periode', function () {
    PeriodePenilaian::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get('/admin/periode')
        ->assertOk();
});

// Store
it('Super Admin dapat tambah periode baru', function () {
    $this->actingAs($this->admin)
        ->post('/admin/periode', [
            'tahun' => 2025,
            'nama' => 'Penilaian 2025',
            'tanggal_mulai' => '2025-06-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'draft',
        ])
        ->assertRedirect('/admin/periode')
        ->assertSessionHas('success');

    expect(PeriodePenilaian::query()->where('nama', 'Penilaian 2025')->exists())->toBeTrue();
});

// Update
it('Super Admin dapat update periode', function () {
    $periode = PeriodePenilaian::factory()->create(['nama' => 'Lama']);

    $this->actingAs($this->admin)
        ->put("/admin/periode/{$periode->id}", [
            'tahun' => $periode->tahun,
            'nama' => 'Updated',
            'tanggal_mulai' => $periode->tanggal_mulai->toDateString(),
            'tanggal_selesai' => $periode->tanggal_selesai->toDateString(),
            'status' => $periode->status->value,
        ])
        ->assertRedirect('/admin/periode')
        ->assertSessionHas('success');

    expect($periode->fresh()->nama)->toBe('Updated');
});

// Destroy
it('Super Admin dapat hapus periode tanpa relasi', function () {
    $periode = PeriodePenilaian::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/periode/{$periode->id}")
        ->assertRedirect('/admin/periode')
        ->assertSessionHas('success');
});

it('blokir hapus periode yang punya data kuesioner', function () {
    $periode = PeriodePenilaian::factory()->create();
    Kuesioner::factory()->create(['periode_id' => $periode->id]);

    $this->actingAs($this->admin)
        ->delete("/admin/periode/{$periode->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
});
