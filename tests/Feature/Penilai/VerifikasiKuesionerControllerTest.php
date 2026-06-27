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

    $this->admin = User::factory()->superAdmin()->create();
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

it('Staff Penilaian dapat lihat list jawaban kuesioner final di periode aktif', function () {
    $this->actingAs($this->penilai)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertOk()
        ->assertSee($this->desa->nama)
        ->assertSee($this->kuesioner->pertanyaan);
});

it('Super Admin dapat lihat list verifikasi kuesioner', function () {
    $this->actingAs($this->admin)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertOk()
        ->assertSee($this->desa->nama);
});

it('Pimpinan dan Staff Admin Desa tidak bisa akses halaman verifikasi kuesioner', function () {
    $pimpinan = User::factory()->pimpinan()->create();
    $staffDesa = User::factory()->staffAdminDesa()->create();

    $this->actingAs($pimpinan)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertForbidden();

    $this->actingAs($staffDesa)
        ->get('/penilai/verifikasi-kuesioner')
        ->assertForbidden();
});

it('tombol Verifikasi selalu muncul dan tidak ada tombol Jadwalkan Dulu', function () {
    $response = $this->actingAs($this->penilai)
        ->get('/penilai/verifikasi-kuesioner');

    $response->assertOk();
    $response->assertSee('Verifikasi');
    $response->assertDontSee('Jadwalkan Dulu');

    $expectedUrl = route('penilai.verifikasi-kuesioner.edit', [
        $this->desa->id,
        $this->periode->id,
    ]);

    $response->assertSee($expectedUrl, false);
});

it('Staff Penilaian dapat buka form verifikasi tanpa jadwal visitasi', function () {
    $this->actingAs($this->penilai)
        ->get("/penilai/verifikasi-kuesioner/{$this->desa->id}/{$this->periode->id}")
        ->assertOk()
        ->assertSee($this->desa->nama)
        ->assertSee($this->periode->nama)
        ->assertSee($this->kuesioner->pertanyaan);
});

it('Super Admin dapat buka form verifikasi', function () {
    $this->actingAs($this->admin)
        ->get("/penilai/verifikasi-kuesioner/{$this->desa->id}/{$this->periode->id}")
        ->assertOk()
        ->assertSee($this->desa->nama);
});

it('update menyimpan verifikasi dengan key desa + periode + kuesioner', function () {
    $this->actingAs($this->penilai)
        ->followingRedirects()
        ->put("/penilai/verifikasi-kuesioner/{$this->desa->id}/{$this->periode->id}", [
            'verifikasi' => [
                [
                    'kuesioner_id' => $this->kuesioner->id,
                    'status_verifikasi' => 'disetujui',
                    'catatan' => 'Sudah sesuai',
                ],
            ],
        ])
        ->assertOk()
        ->assertSee('Verifikasi kuesioner berhasil disimpan.');

    expect(VerifikasiKuesioner::query()->where('desa_id', $this->desa->id)
        ->where('periode_id', $this->periode->id)
        ->where('kuesioner_id', $this->kuesioner->id)
        ->exists())->toBeTrue();
});

it('Staff Penilaian non-super-admin bisa verifikasi desa yang tidak ditugaskan jadwal', function () {
    $penilaiLain = User::factory()->staffPenilaian()->create();

    $this->actingAs($penilaiLain)
        ->get("/penilai/verifikasi-kuesioner/{$this->desa->id}/{$this->periode->id}")
        ->assertOk();
});

it('update mengupdate verifikasi existing', function () {
    VerifikasiKuesioner::create([
        'desa_id' => $this->desa->id,
        'periode_id' => $this->periode->id,
        'kuesioner_id' => $this->kuesioner->id,
        'diverifikasi_oleh' => $this->penilai->id,
        'status_verifikasi' => 'ditolak',
    ]);

    $this->actingAs($this->penilai)
        ->followingRedirects()
        ->put("/penilai/verifikasi-kuesioner/{$this->desa->id}/{$this->periode->id}", [
            'verifikasi' => [
                [
                    'kuesioner_id' => $this->kuesioner->id,
                    'status_verifikasi' => 'perlu_perbaikan',
                    'catatan' => 'Perlu revisi',
                ],
            ],
        ])
        ->assertOk()
        ->assertSee('Verifikasi kuesioner berhasil disimpan.');

    $verifikasi = VerifikasiKuesioner::query()
        ->where('desa_id', $this->desa->id)
        ->where('periode_id', $this->periode->id)
        ->where('kuesioner_id', $this->kuesioner->id)
        ->first();

    expect($verifikasi)
        ->status_verifikasi->toBe('perlu_perbaikan')
        ->catatan->toBe('Perlu revisi');
});
