<?php

namespace Database\Seeders;

use App\Enums\StatusPeriode;
use App\Models\Desa;
use App\Models\JadwalVisitasi;
use App\Models\JawabanKuesioner;
use App\Models\Kuesioner;
use App\Models\NilaiAkhir;
use App\Models\PenilaianVisitasi;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Models\VerifikasiKuesioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $penilaiUser = User::where('username', 'penilai')->firstOrFail();
        $staffDesaUser = User::where('username', 'staffdesa')->firstOrFail();
        $superAdminUser = User::where('username', 'superadmin')->firstOrFail();

        $desaList = Desa::where('is_active', true)->orderBy('id')->get();

        // 1) Periode aktif 2026
        $periode = PeriodePenilaian::create([
            'tahun' => 2026,
            'nama' => 'Penilaian Apresiasi Desa 2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => StatusPeriode::Aktif->value,
        ]);

        // 2) Kuesioner — 5 pertanyaan, total bobot = 100
        $kuesionerData = [
            ['kode_indikator' => 'K-TR-01', 'kategori' => 'Transparansi', 'pertanyaan' => 'Apakah desa memiliki papan informasi publik yang memuat APBDes secara berkala?', 'bobot_indikator' => 20, 'urutan' => 1],
            ['kode_indikator' => 'K-TR-02', 'kategori' => 'Transparansi', 'pertanyaan' => 'Apakah laporan keuangan desa dipublikasikan melalui website atau media sosial resmi desa?', 'bobot_indikator' => 20, 'urutan' => 2],
            ['kode_indikator' => 'K-PA-01', 'kategori' => 'Partisipasi', 'pertanyaan' => 'Apakah desa rutin mengadakan musyawarah desa minimal 3 kali setahun?', 'bobot_indikator' => 20, 'urutan' => 3],
            ['kode_indikator' => 'K-PL-01', 'kategori' => 'Pelayanan', 'pertanyaan' => 'Apakah desa memiliki standar pelayanan publik yang tertulis dan dipublikasikan?', 'bobot_indikator' => 20, 'urutan' => 4],
            ['kode_indikator' => 'K-PL-02', 'kategori' => 'Pelayanan', 'pertanyaan' => 'Apakah desa menyediakan mekanisme pengaduan masyarakat yang responsif?', 'bobot_indikator' => 20, 'urutan' => 5],
        ];

        $kuesionerList = collect();
        foreach ($kuesionerData as $data) {
            $data['periode_id'] = $periode->id;
            $data['is_active'] = true;
            $kuesionerList->push(Kuesioner::create($data));
        }

        foreach ($desaList as $desa) {
            // 3) Jawaban kuesioner dari staff desa (final)
            foreach ($kuesionerList as $kues) {
                JawabanKuesioner::create([
                    'desa_id' => $desa->id,
                    'kuesioner_id' => $kues->id,
                    'periode_id' => $periode->id,
                    'jawaban' => "Jawaban {$desa->nama} untuk {$kues->kode_indikator}",
                    'skor' => fake()->numberBetween(60, 95),
                    'keterangan' => fake()->optional(0.3)->sentence(),
                    'status' => 'final',
                    'diisi_oleh' => $staffDesaUser->id,
                ]);
            }

            // 4) Jadwal visitasi — status selesai
            $jadwal = JadwalVisitasi::create([
                'desa_id' => $desa->id,
                'periode_id' => $periode->id,
                'tanggal_visitasi' => fake()->dateTimeBetween('2026-03-01', '2026-05-31')->format('Y-m-d'),
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '12:00',
                'lokasi' => "Kantor {$desa->nama}",
                'petugas_id' => $penilaiUser->id,
                'status' => 'selesai',
                'catatan' => "Visitasi {$desa->nama} telah selesai dilaksanakan.",
                'dibuat_oleh' => $superAdminUser->id,
            ]);

            // 5) Penilaian visitasi — 5 indikator
            $templateIndikator = config('penilaian_visitasi.indikator', []);
            foreach ($templateIndikator as $item) {
                PenilaianVisitasi::create([
                    'jadwal_id' => $jadwal->id,
                    'desa_id' => $desa->id,
                    'periode_id' => $periode->id,
                    'indikator_visitasi' => $item['nama'],
                    'skor' => fake()->numberBetween(50, 90),
                    'bobot' => $item['bobot'],
                    'keterangan' => fake()->optional()->sentence(),
                    'dinilai_oleh' => $penilaiUser->id,
                    'tanggal_input' => now(),
                ]);
            }

            // 6) Verifikasi kuesioner
            foreach ($kuesionerList as $kues) {
                VerifikasiKuesioner::create([
                    'jadwal_id' => $jadwal->id,
                    'desa_id' => $desa->id,
                    'periode_id' => $periode->id,
                    'kuesioner_id' => $kues->id,
                    'status_verifikasi' => fake()->randomElement(['ya', 'tidak']),
                    'catatan' => fake()->optional(0.4)->sentence(),
                    'diverifikasi_oleh' => $penilaiUser->id,
                    'tanggal_verifikasi' => now(),
                ]);
            }
        }

        // 7) Nilai akhir — hitung per desa
        foreach ($desaList as $desa) {
            $nilaiKuesioner = (float) JawabanKuesioner::query()
                ->where('jawaban_kuesioner.desa_id', $desa->id)
                ->where('jawaban_kuesioner.periode_id', $periode->id)
                ->join('kuesioner', 'jawaban_kuesioner.kuesioner_id', '=', 'kuesioner.id')
                ->sum(DB::raw('jawaban_kuesioner.skor * kuesioner.bobot_indikator / 100'));

            $nilaiVisitasi = (float) PenilaianVisitasi::query()
                ->where('desa_id', $desa->id)
                ->where('periode_id', $periode->id)
                ->sum(DB::raw('skor * bobot / 100'));

            $nilaiAkhir = round(($nilaiKuesioner * 0.6) + ($nilaiVisitasi * 0.4), 2);

            NilaiAkhir::create([
                'desa_id' => $desa->id,
                'periode_id' => $periode->id,
                'nilai_kuesioner' => round($nilaiKuesioner, 2),
                'nilai_visitasi' => round($nilaiVisitasi, 2),
                'nilai_akhir' => $nilaiAkhir,
                'peringkat' => null,
                'dihitung_pada' => now(),
                'dihitung_oleh' => $superAdminUser->id,
            ]);
        }

        // Assign peringkat berdasarkan nilai_akhir DESC
        $ranked = NilaiAkhir::where('periode_id', $periode->id)
            ->orderByDesc('nilai_akhir')
            ->get();

        $rank = 1;
        foreach ($ranked as $row) {
            $row->update(['peringkat' => $rank++]);
        }

        $this->command?->info('Dummy data berhasil dibuat: 1 periode, 5 kuesioner, 3 desa lengkap.');
    }
}
