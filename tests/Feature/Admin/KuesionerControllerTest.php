<?php

use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
    $this->periode = PeriodePenilaian::factory()->aktif()->create();
});

// Index
it('Super Admin dapat lihat list kuesioner per periode', function () {
    Kuesioner::factory()->count(3)->create(['periode_id' => $this->periode->id]);

    $this->actingAs($this->admin)
        ->get('/admin/kuesioner?periode='.$this->periode->id)
        ->assertOk()
        ->assertSee($this->periode->nama);
});

// Store
it('Super Admin dapat tambah indikator kuesioner', function () {
    $this->actingAs($this->admin)
        ->post('/admin/kuesioner', [
            'periode_id' => $this->periode->id,
            'kategori' => 'Transparansi',
            'kode_indikator' => 'K-01',
            'pertanyaan' => 'Apakah desa mempublikasikan APBDesa?',
            'bobot_indikator' => 25,
            'urutan' => 1,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Kuesioner::query()->where('kode_indikator', 'K-01')->exists())->toBeTrue();
});

it('tolak total bobot melebihi 100', function () {
    Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'bobot_indikator' => 80,
    ]);

    $this->actingAs($this->admin)
        ->post('/admin/kuesioner', [
            'periode_id' => $this->periode->id,
            'kategori' => 'Transparansi',
            'kode_indikator' => 'K-02',
            'pertanyaan' => 'Pertanyaan kedua',
            'bobot_indikator' => 30,
            'urutan' => 2,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('bobot_indikator');
});

// Update
it('Super Admin dapat update indikator kuesioner', function () {
    $kuesioner = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'pertanyaan' => 'Pertanyaan lama',
    ]);

    $this->actingAs($this->admin)
        ->put("/admin/kuesioner/{$kuesioner->id}", [
            'periode_id' => $this->periode->id,
            'kategori' => $kuesioner->kategori,
            'kode_indikator' => $kuesioner->kode_indikator,
            'pertanyaan' => 'Pertanyaan baru',
            'bobot_indikator' => $kuesioner->bobot_indikator,
            'urutan' => $kuesioner->urutan,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($kuesioner->fresh()->pertanyaan)->toBe('Pertanyaan baru');
});

// Destroy
it('Super Admin dapat hapus indikator tanpa jawaban', function () {
    $kuesioner = Kuesioner::factory()->create(['periode_id' => $this->periode->id]);

    $this->actingAs($this->admin)
        ->delete("/admin/kuesioner/{$kuesioner->id}")
        ->assertRedirect()
        ->assertSessionHas('success');
});

it('blokir hapus indikator yang sudah punya jawaban', function () {
    $kuesioner = Kuesioner::factory()->create(['periode_id' => $this->periode->id]);
    $desa = Desa::factory()->create();

    JawabanKuesioner::factory()->create([
        'kuesioner_id' => $kuesioner->id,
        'periode_id' => $this->periode->id,
        'desa_id' => $desa->id,
        'diisi_oleh' => $this->admin->id,
    ]);

    $this->actingAs($this->admin)
        ->delete("/admin/kuesioner/{$kuesioner->id}")
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('menampilkan kuesioner terurut by urutan meski kategori non-alfabet', function () {
    Kuesioner::factory()->create(['periode_id' => $this->periode->id, 'kategori' => 'Transparansi', 'kode_indikator' => 'K-TR-01', 'urutan' => 1, 'is_active' => true]);
    Kuesioner::factory()->create(['periode_id' => $this->periode->id, 'kategori' => 'Transparansi', 'kode_indikator' => 'K-TR-02', 'urutan' => 2, 'is_active' => true]);
    Kuesioner::factory()->create(['periode_id' => $this->periode->id, 'kategori' => 'Partisipasi', 'kode_indikator' => 'K-PA-01', 'urutan' => 3, 'is_active' => true]);
    Kuesioner::factory()->create(['periode_id' => $this->periode->id, 'kategori' => 'Pelayanan', 'kode_indikator' => 'K-PL-01', 'urutan' => 4, 'is_active' => true]);
    Kuesioner::factory()->create(['periode_id' => $this->periode->id, 'kategori' => 'Pelayanan', 'kode_indikator' => 'K-PL-02', 'urutan' => 5, 'is_active' => true]);

    $response = $this->actingAs($this->admin)
        ->get('/admin/kuesioner?periode='.$this->periode->id)
        ->assertOk();

    $response->assertSeeInOrder(['#1', '#2', '#3', '#4', '#5']);
});
