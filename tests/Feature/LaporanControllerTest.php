<?php

use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->superAdmin()->create();
    $this->periode = PeriodePenilaian::factory()->aktif()->create();
});

it('Super Admin dapat akses halaman laporan', function () {
    $this->actingAs($this->admin)
        ->get('/laporan')
        ->assertOk();
});

it('Super Admin dapat cetak laporan rekapitulasi PDF', function () {
    $desa = Desa::factory()->create();
    NilaiAkhir::factory()->create([
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
        'nilai_kuesioner' => 80,
        'nilai_visitasi' => 70,
        'nilai_akhir' => 76,
        'peringkat' => 1,
    ]);

    $this->actingAs($this->admin)
        ->get("/laporan/rekapitulasi/{$this->periode->id}")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('Super Admin dapat cetak laporan per desa PDF', function () {
    $desa = Desa::factory()->create();
    $kuesioner = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'bobot_indikator' => 100,
    ]);

    JawabanKuesioner::factory()->create([
        'desa_id' => $desa->id,
        'kuesioner_id' => $kuesioner->id,
        'periode_id' => $this->periode->id,
        'skor' => 80,
    ]);

    $nilai = NilaiAkhir::factory()->create([
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
    ]);

    $this->actingAs($this->admin)
        ->get("/laporan/per-desa/{$nilai->id}")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('blokir non-Super Admin cetak audit trail', function () {
    $pimpinan = User::factory()->pimpinan()->create();

    $this->actingAs($pimpinan)
        ->get('/laporan/audit-trail')
        ->assertForbidden();
});

it('Staff Admin Desa hanya boleh cetak laporan desanya sendiri', function () {
    $desaA = Desa::factory()->create();
    $desaB = Desa::factory()->create();
    $user = User::factory()->staffAdminDesa()->create(['desa_id' => $desaA->id]);

    $nilaiB = NilaiAkhir::factory()->create([
        'desa_id' => $desaB->id,
        'periode_id' => $this->periode->id,
    ]);

    $this->actingAs($user)
        ->get("/laporan/per-desa/{$nilaiB->id}")
        ->assertForbidden();
});
