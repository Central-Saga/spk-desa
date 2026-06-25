<?php

use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\PerhitunganNilaiService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->superAdmin()->create();
    $this->periode = PeriodePenilaian::factory()->aktif()->create();
    $this->service = app(PerhitunganNilaiService::class);
});

/**
 * Helper: bikin kuesioner periode dengan total bobot 100 (4 indikator @ 25).
 *
 * @return Collection<int, Kuesioner>
 */
function seedKuesionerPenuh(PeriodePenilaian $periode): Collection
{
    return collect(range(1, 4))->map(fn (int $i) => Kuesioner::factory()->create([
        'periode_id' => $periode->id,
        'kode_indikator' => "K-{$i}",
        'bobot_indikator' => 25,
        'urutan' => $i,
    ]));
}

/**
 * Helper: bikin jawaban kuesioner desa dengan skor seragam.
 */
function seedJawaban(Desa $desa, PeriodePenilaian $periode, Collection $kues, float $skor, User $admin): void
{
    foreach ($kues as $k) {
        JawabanKuesioner::create([
            'desa_id' => $desa->id,
            'kuesioner_id' => $k->id,
            'periode_id' => $periode->id,
            'skor' => $skor,
            'status' => 'final',
            'diisi_oleh' => $admin->id,
        ]);
    }
}

/**
 * Helper: bikin jadwal + 5 indikator visitasi dengan total bobot 100.
 */
function seedVisitasi(Desa $desa, PeriodePenilaian $periode, float $skor, User $admin): void
{
    $jadwal = JadwalVisitasi::create([
        'desa_id' => $desa->id,
        'periode_id' => $periode->id,
        'tanggal_visitasi' => now()->addDays(7),
        'waktu_mulai' => '09:00',
        'lokasi' => 'Kantor '.$desa->nama,
        'petugas_id' => $admin->id,
        'status' => 'selesai',
        'dibuat_oleh' => $admin->id,
    ]);

    foreach ([20, 25, 25, 15, 15] as $idx => $bobot) {
        PenilaianVisitasi::create([
            'jadwal_id' => $jadwal->id,
            'desa_id' => $desa->id,
            'periode_id' => $periode->id,
            'indikator_visitasi' => "Indikator V-{$idx}",
            'skor' => $skor,
            'bobot' => $bobot,
            'dinilai_oleh' => $admin->id,
            'tanggal_input' => now(),
        ]);
    }
}

it('menghitung nilai akhir dengan bobot 60% kuesioner + 40% visitasi', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);

    // skor kuesioner 80 → nilai = sum(80*25/100) * 4 = 80
    seedJawaban($desa, $this->periode, $kues, 80, $this->admin);

    // skor visitasi 70 → nilai = sum(70*bobot/100) untuk bobot total 100 = 70
    seedVisitasi($desa, $this->periode, 70, $this->admin);

    $hasil = $this->service->hitungSatuDesa($desa, $this->periode, $this->admin);

    // (80 * 0.6) + (70 * 0.4) = 48 + 28 = 76
    expect($hasil)
        ->nilai_kuesioner->toEqual(80.00)
        ->nilai_visitasi->toEqual(70.00)
        ->nilai_akhir->toEqual(76.00);
});

it('menetapkan peringkat berdasarkan nilai akhir desc', function () {
    [$desaA, $desaB, $desaC] = Desa::factory()->count(3)->create();
    $kues = seedKuesionerPenuh($this->periode);

    seedJawaban($desaA, $this->periode, $kues, 90, $this->admin); // 90
    seedVisitasi($desaA, $this->periode, 80, $this->admin); // (90*0.6)+(80*0.4)=54+32=86

    seedJawaban($desaB, $this->periode, $kues, 70, $this->admin);
    seedVisitasi($desaB, $this->periode, 60, $this->admin); // 42+24=66

    seedJawaban($desaC, $this->periode, $kues, 80, $this->admin);
    seedVisitasi($desaC, $this->periode, 70, $this->admin); // 48+28=76

    $this->service->hitungSemuaDesa($this->periode, $this->admin);

    expect(NilaiAkhir::where('desa_id', $desaA->id)->first()->peringkat)->toBe(1);
    expect(NilaiAkhir::where('desa_id', $desaC->id)->first()->peringkat)->toBe(2);
    expect(NilaiAkhir::where('desa_id', $desaB->id)->first()->peringkat)->toBe(3);
});

it('idempotent: trigger ulang tidak menggandakan record', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);
    seedJawaban($desa, $this->periode, $kues, 80, $this->admin);
    seedVisitasi($desa, $this->periode, 70, $this->admin);

    $this->service->hitungSemuaDesa($this->periode, $this->admin);

    $this->service->hitungSemuaDesa($this->periode, $this->admin);

    expect(NilaiAkhir::query()->where('desa_id', $desa->id)->count())->toBe(1);
});

it('mengembalikan nol untuk desa tanpa data jawaban dan visitasi', function () {
    $desa = Desa::factory()->create();

    $hasil = $this->service->hitungSatuDesa($desa, $this->periode, $this->admin);

    expect($hasil->nilai_kuesioner)->toEqual(0.00);
    expect($hasil->nilai_visitasi)->toEqual(0.00);
    expect($hasil->nilai_akhir)->toEqual(0.00);
});

it('cekKelengkapan menandai data lengkap saat semua indikator dijawab', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);
    seedJawaban($desa, $this->periode, $kues, 80, $this->admin);
    seedVisitasi($desa, $this->periode, 70, $this->admin);

    $cek = $this->service->cekKelengkapan($desa, $this->periode);

    expect($cek['kuesioner_lengkap'])->toBeTrue();
    expect($cek['visitasi_lengkap'])->toBeTrue();
    expect($cek['kuesioner_terjawab'])->toBe(4);
    expect($cek['visitasi_dinilai'])->toBe(1);
});

it('cekKelengkapan menandai belum lengkap saat hanya sebagian indikator dijawab', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);
    seedJawaban($desa, $this->periode, $kues->take(2), 80, $this->admin);

    $cek = $this->service->cekKelengkapan($desa, $this->periode);

    expect($cek['kuesioner_lengkap'])->toBeFalse();
    expect($cek['kuesioner_terjawab'])->toBe(2);
    expect($cek['total_kuesioner'])->toBe(4);
});
