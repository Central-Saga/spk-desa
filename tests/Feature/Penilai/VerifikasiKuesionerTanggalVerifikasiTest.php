<?php

use App\Models\Desa;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Models\VerifikasiKuesioner;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->seed(RolePermissionSeeder::class);

    $this->penilai = User::factory()->staffPenilaian()->create();
    $this->periode = PeriodePenilaian::factory()->aktif()->create();
    $this->desa = Desa::factory()->create();
    $this->kuesioner = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'bobot_indikator' => 25,
        'urutan' => 1,
    ]);

    JawabanKuesioner::factory()->final()->create([
        'desa_id' => $this->desa->id,
        'kuesioner_id' => $this->kuesioner->id,
        'periode_id' => $this->periode->id,
        'diisi_oleh' => User::factory()->staffAdminDesa()->create()->id,
    ]);
});

it('menampilkan tanggal verifikasi yang sudah ada dalam format d M Y', function () {
    VerifikasiKuesioner::create([
        'desa_id' => $this->desa->id,
        'periode_id' => $this->periode->id,
        'kuesioner_id' => $this->kuesioner->id,
        'diverifikasi_oleh' => $this->penilai->id,
        'status_verifikasi' => 'disetujui',
        'tanggal_verifikasi' => '2026-06-30 14:30:00',
    ]);

    $this->actingAs($this->penilai)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertOk()
        ->assertSee('30 Jun 2026')
        ->assertSee('Selesai');
});

it('menampilkan tanda minus saat belum ada verifikasi', function () {
    $response = $this->actingAs($this->penilai)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertOk()
        ->assertSee('Belum');

    expect($response->getContent())->toContain('-');
});

it('badge Sebagian muncul saat hanya sebagian pertanyaan diverifikasi', function () {
    $kuesioner2 = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'bobot_indikator' => 25,
        'urutan' => 2,
    ]);
    JawabanKuesioner::factory()->final()->create([
        'desa_id' => $this->desa->id,
        'kuesioner_id' => $kuesioner2->id,
        'periode_id' => $this->periode->id,
        'diisi_oleh' => User::factory()->staffAdminDesa()->create()->id,
    ]);

    VerifikasiKuesioner::create([
        'desa_id' => $this->desa->id,
        'periode_id' => $this->periode->id,
        'kuesioner_id' => $this->kuesioner->id,
        'diverifikasi_oleh' => $this->penilai->id,
        'status_verifikasi' => 'disetujui',
        'tanggal_verifikasi' => '2026-06-30 14:30:00',
    ]);

    $this->actingAs($this->penilai)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertOk()
        ->assertSee('Sebagian (1/2)');
});
