<?php

use App\Models\Desa;
use App\Models\IndikatorVisitasi;
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
 * Helper: bikin jadwal + 5 indikator visitasi global dengan total bobot 100.
 */
function seedVisitasi(Desa $desa, PeriodePenilaian $periode, float $skor, User $admin): JadwalVisitasi
{
    $suffix = fake()->unique()->numberBetween(1000, 9999);
    $indikatorData = [
        ['kode' => "V-{$suffix}-01", 'indikator_visitasi' => "Indikator V-{$suffix}-0", 'bobot' => 20, 'urutan' => 1],
        ['kode' => "V-{$suffix}-02", 'indikator_visitasi' => "Indikator V-{$suffix}-1", 'bobot' => 25, 'urutan' => 2],
        ['kode' => "V-{$suffix}-03", 'indikator_visitasi' => "Indikator V-{$suffix}-2", 'bobot' => 25, 'urutan' => 3],
        ['kode' => "V-{$suffix}-04", 'indikator_visitasi' => "Indikator V-{$suffix}-3", 'bobot' => 15, 'urutan' => 4],
        ['kode' => "V-{$suffix}-05", 'indikator_visitasi' => "Indikator V-{$suffix}-4", 'bobot' => 15, 'urutan' => 5],
    ];

    foreach ($indikatorData as $data) {
        IndikatorVisitasi::factory()->create([
            'periode_id' => $periode->id,
            'desa_id' => null,
            'kode' => $data['kode'],
            'indikator_visitasi' => $data['indikator_visitasi'],
            'bobot' => $data['bobot'],
            'urutan' => $data['urutan'],
            'is_active' => true,
        ]);
    }

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

    foreach ($indikatorData as $idx => $data) {
        PenilaianVisitasi::create([
            'jadwal_id' => $jadwal->id,
            'desa_id' => $desa->id,
            'periode_id' => $periode->id,
            'indikator_visitasi' => $data['indikator_visitasi'],
            'skor' => $skor,
            'bobot' => $data['bobot'],
            'dinilai_oleh' => $admin->id,
            'tanggal_input' => now(),
        ]);
    }

    return $jadwal;
}

/**
 * Helper: bikin penilaian visitasi berdasarkan indikator yang diberikan.
 */
function seedVisitasiCustom(Desa $desa, PeriodePenilaian $periode, array $indikatorList, float $skor, User $admin): JadwalVisitasi
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

    foreach ($indikatorList as $indikator) {
        PenilaianVisitasi::create([
            'jadwal_id' => $jadwal->id,
            'desa_id' => $desa->id,
            'periode_id' => $periode->id,
            'indikator_visitasi' => $indikator['indikator_visitasi'],
            'skor' => $skor,
            'bobot' => $indikator['bobot'],
            'dinilai_oleh' => $admin->id,
            'tanggal_input' => now(),
        ]);
    }

    return $jadwal;
}

it('menghitung nilai akhir dengan bobot 60% kuesioner + 40% visitasi', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);

    seedJawaban($desa, $this->periode, $kues, 80, $this->admin);
    seedVisitasi($desa, $this->periode, 70, $this->admin);

    $hasil = $this->service->hitungSatuDesa($desa, $this->periode, $this->admin);

    expect($hasil)
        ->nilai_kuesioner->toEqual(80.00)
        ->nilai_visitasi->toEqual(70.00)
        ->nilai_akhir->toEqual(76.00);
});

it('menetapkan peringkat berdasarkan nilai akhir desc', function () {
    [$desaA, $desaB, $desaC] = Desa::factory()->count(3)->create();
    $kues = seedKuesionerPenuh($this->periode);

    seedJawaban($desaA, $this->periode, $kues, 90, $this->admin);
    seedVisitasi($desaA, $this->periode, 80, $this->admin);

    seedJawaban($desaB, $this->periode, $kues, 70, $this->admin);
    seedVisitasi($desaB, $this->periode, 60, $this->admin);

    seedJawaban($desaC, $this->periode, $kues, 80, $this->admin);
    seedVisitasi($desaC, $this->periode, 70, $this->admin);

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
    expect($cek['visitasi_dinilai'])->toBe(5);
    expect($cek['total_visitasi'])->toBe(5);
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

it('cekKelengkapan mengabaikan penilaian orphaned yang tidak ada di template aktif', function () {
    $desa = Desa::factory()->create();
    $kues = seedKuesionerPenuh($this->periode);
    seedJawaban($desa, $this->periode, $kues, 80, $this->admin);

    $jadwal = JadwalVisitasi::factory()->create([
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
        'petugas_id' => $this->admin->id,
        'dibuat_oleh' => $this->admin->id,
        'status' => 'selesai',
    ]);

    foreach (range(1, 3) as $i) {
        PenilaianVisitasi::factory()->create([
            'jadwal_id' => $jadwal->id,
            'desa_id' => $desa->id,
            'periode_id' => $this->periode->id,
            'indikator_visitasi' => "Indikator Lama Orphaned {$i}",
            'skor' => 70,
            'bobot' => 25,
            'dinilai_oleh' => $this->admin->id,
        ]);
    }

    $cek = $this->service->cekKelengkapan($desa, $this->periode);

    expect($cek['visitasi_lengkap'])->toBeFalse();
    expect($cek['visitasi_dinilai'])->toBe(0);
    expect($cek['total_visitasi'])->toBe(0);
});

it('hitungNilaiVisitasi hanya menjumlahkan record yang cocok template aktif', function () {
    $desa = Desa::factory()->create();

    $jadwal = JadwalVisitasi::factory()->create([
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
        'petugas_id' => $this->admin->id,
        'dibuat_oleh' => $this->admin->id,
        'status' => 'selesai',
    ]);

    // Template aktif: 1 indikator @ bobot 100
    IndikatorVisitasi::factory()->create([
        'periode_id' => $this->periode->id,
        'desa_id' => null,
        'kode' => 'V-ACTIVE',
        'indikator_visitasi' => 'Indikator Aktif',
        'bobot' => 100,
        'is_active' => true,
    ]);

    // Match template: skor 80 * 100 / 100 = 80
    PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
        'indikator_visitasi' => 'Indikator Aktif',
        'skor' => 80,
        'bobot' => 100,
        'dinilai_oleh' => $this->admin->id,
    ]);

    // Orphaned: tidak ikut dihitung
    PenilaianVisitasi::factory()->create([
        'jadwal_id' => $jadwal->id,
        'desa_id' => $desa->id,
        'periode_id' => $this->periode->id,
        'indikator_visitasi' => 'Indikator Lama',
        'skor' => 100,
        'bobot' => 100,
        'dinilai_oleh' => $this->admin->id,
    ]);

    $hasil = $this->service->hitungSatuDesa($desa, $this->periode, $this->admin);

    expect($hasil->nilai_visitasi)->toEqual(80.00);
});

it('hitungNilaiKuesioner tidak menjumlahkan jawaban untuk kuesioner nonaktif', function () {
    $desa = Desa::factory()->create();

    $kuesAktif = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'kode_indikator' => 'K-AKTIF',
        'bobot_indikator' => 100,
        'is_active' => true,
    ]);

    $kuesNonaktif = Kuesioner::factory()->create([
        'periode_id' => $this->periode->id,
        'kode_indikator' => 'K-NONAKTIF',
        'bobot_indikator' => 100,
        'is_active' => false,
    ]);

    JawabanKuesioner::create([
        'desa_id' => $desa->id,
        'kuesioner_id' => $kuesAktif->id,
        'periode_id' => $this->periode->id,
        'skor' => 80,
        'status' => 'final',
        'diisi_oleh' => $this->admin->id,
    ]);

    JawabanKuesioner::create([
        'desa_id' => $desa->id,
        'kuesioner_id' => $kuesNonaktif->id,
        'periode_id' => $this->periode->id,
        'skor' => 100,
        'status' => 'final',
        'diisi_oleh' => $this->admin->id,
    ]);

    $hasil = $this->service->hitungSatuDesa($desa, $this->periode, $this->admin);

    expect($hasil->nilai_kuesioner)->toEqual(80.00);
});
