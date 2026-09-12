<?php

namespace Database\Seeders;

use App\Enums\StatusPeriode;
use App\Models\Desa;
use App\Models\IndikatorVisitasi;
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

        // 2b) Indikator Visitasi — 5 indikator GLOBAL, total bobot = 100 (berlaku untuk semua desa)
        $visitasiGlobalData = [
            ['kode' => 'V-OBSV-01', 'kategori' => 'Observasi', 'indikator_visitasi' => 'Kondisi Fisik Kantor Desa', 'deskripsi' => 'Kelayakan dan kebersihan kantor desa sebagai pusat pelayanan publik.', 'bobot' => 20, 'urutan' => 1],
            ['kode' => 'V-OBSV-02', 'kategori' => 'Observasi', 'indikator_visitasi' => 'Sarana Layanan Informasi', 'deskripsi' => 'Keberadaan papan informasi, layar publik, atau media transparansi anggaran.', 'bobot' => 25, 'urutan' => 2],
            ['kode' => 'V-OBSV-03', 'kategori' => 'Observasi', 'indikator_visitasi' => 'Wawancara Warga & Aparatur', 'deskripsi' => 'Tingkat kepuasan warga atas keterbukaan informasi desa.', 'bobot' => 25, 'urutan' => 3],
            ['kode' => 'V-OBSV-04', 'kategori' => 'Observasi', 'indikator_visitasi' => 'Dokumentasi Musyawarah Desa', 'deskripsi' => 'Bukti notulen, daftar hadir, dan dokumentasi musyawarah desa.', 'bobot' => 15, 'urutan' => 4],
            ['kode' => 'V-OBSV-05', 'kategori' => 'Observasi', 'indikator_visitasi' => 'Inovasi Pelayanan', 'deskripsi' => 'Inovasi digital atau pelayanan publik berbasis transparansi informasi.', 'bobot' => 15, 'urutan' => 5],
        ];

        foreach ($visitasiGlobalData as $data) {
            $data['periode_id'] = $periode->id;
            $data['desa_id'] = null;
            $data['is_active'] = true;
            IndikatorVisitasi::create($data);
        }

        // 2c) Indikator Visitasi PER-DESA — khusus untuk Desa Bedugul (bobot sendiri 100)
        $desaBedugul = $desaList->firstWhere('nama', 'Desa Bedugul');
        $visitasiPerDesaData = [
            ['kode' => 'V-BDG-01', 'kategori' => 'Khusus', 'indikator_visitasi' => 'Program Inovasi Informasi Publik', 'deskripsi' => 'Penilaian khusus program inovasi Desa Bedugul dalam keterbukaan informasi publik.', 'bobot' => 40, 'urutan' => 1],
            ['kode' => 'V-BDG-02', 'kategori' => 'Khusus', 'indikator_visitasi' => 'Kesiapan Infrastruktur Digital', 'deskripsi' => 'Kesiapan Desa Bedugul dalam mengadopsi layanan informasi berbasis digital.', 'bobot' => 30, 'urutan' => 2],
            ['kode' => 'V-BDG-03', 'kategori' => 'Khusus', 'indikator_visitasi' => 'Keaktifan Partisipasi Masyarakat', 'deskripsi' => 'Tingkat partisipasi masyarakat Desa Bedugul dalam musyawarah desa.', 'bobot' => 30, 'urutan' => 3],
        ];

        foreach ($visitasiPerDesaData as $data) {
            $data['periode_id'] = $periode->id;
            $data['desa_id'] = $desaBedugul->id;
            $data['is_active'] = true;
            IndikatorVisitasi::create($data);
        }

        // Jawaban deskriptif per desa per pertanyaan
        $jawabanDeskriptif = [
            'Desa Bedugul' => [
                'K-TR-01' => ['jawaban' => 'Papan informasi tersedia di depan kantor desa dan diperbarui setiap bulan oleh staf administrasi.', 'status_jawaban' => 'iya'],
                'K-TR-02' => ['jawaban' => 'Laporan keuangan sudah dipublikasikan melalui website resmi desa bedugul.go.id setiap akhir bulan.', 'status_jawaban' => 'iya'],
                'K-PA-01' => ['jawaban' => 'Musyawarah desa rutin dilaksanakan setiap 3 bulan, tercatat dalam notulen resmi desa.', 'status_jawaban' => 'iya'],
                'K-PL-01' => ['jawaban' => 'Standar pelayanan publik sudah tertulis dan dipasang di papan pengumuman kantor desa.', 'status_jawaban' => 'iya'],
                'K-PL-02' => ['jawaban' => 'Tersedia kotak pengaduan dan formulir online di website desa untuk pengaduan masyarakat.', 'status_jawaban' => 'iya'],
            ],
            'Desa Penglipuran' => [
                'K-TR-01' => ['jawaban' => 'Papan informasi sudah tersedia namun belum diperbarui secara rutin, terakhir diperbarui 3 bulan lalu.', 'status_jawaban' => 'iya'],
                'K-TR-02' => ['jawaban' => 'Belum tersedia publikasi laporan keuangan melalui website, hanya ditempel di kantor desa.', 'status_jawaban' => 'tidak'],
                'K-PA-01' => ['jawaban' => 'Musyawarah desa dilakukan 2 kali setahun, belum memenuhi standar minimal 3 kali.', 'status_jawaban' => 'tidak'],
                'K-PL-01' => ['jawaban' => 'Dokumen standar pelayanan masih dalam proses penyusunan oleh perangkat desa.', 'status_jawaban' => 'iya'],
                'K-PL-02' => ['jawaban' => 'Pengaduan masyarakat masih dilakukan secara manual melalui buku tamu di kantor desa.', 'status_jawaban' => 'iya'],
            ],
            'Desa Trunyan' => [
                'K-TR-01' => ['jawaban' => 'Papan informasi tersedia di dua titik strategis desa dan diperbarui setiap bulan.', 'status_jawaban' => 'iya'],
                'K-TR-02' => ['jawaban' => 'Laporan keuangan dipublikasikan melalui grup WhatsApp desa dan papan pengumuman.', 'status_jawaban' => 'iya'],
                'K-PA-01' => ['jawaban' => 'Musyawarah desa rutin dilakukan setiap bulan, didokumentasikan dengan baik.', 'status_jawaban' => 'iya'],
                'K-PL-01' => ['jawaban' => 'Dokumen standar pelayanan belum tersedia secara tertulis, hanya bersifat lisan.', 'status_jawaban' => 'tidak'],
                'K-PL-02' => ['jawaban' => 'Mekanisme pengaduan masih sangat terbatas, warga biasanya langsung melapor ke kepala dusun.', 'status_jawaban' => 'tidak'],
            ],
        ];

        $verifikasiStatuses = ['disetujui', 'ditolak', 'perlu_perbaikan'];

        foreach ($desaList as $desa) {
            $namaSingkat = match ($desa->nama) {
                'Desa Bedugul' => 'Desa Bedugul',
                'Desa Penglipuran' => 'Desa Penglipuran',
                'Desa Trunyan' => 'Desa Trunyan',
                default => $desa->nama,
            };

            // 3) Jawaban kuesioner dari staff desa (final)
            foreach ($kuesionerList as $kues) {
                $dataJawaban = $jawabanDeskriptif[$namaSingkat][$kues->kode_indikator] ?? null;

                JawabanKuesioner::create([
                    'desa_id' => $desa->id,
                    'kuesioner_id' => $kues->id,
                    'periode_id' => $periode->id,
                    'jawaban' => $dataJawaban['jawaban'] ?? 'Belum ada jawaban.',
                    'status_jawaban' => $dataJawaban['status_jawaban'] ?? 'iya',
                    'skor' => fake()->numberBetween(60, 95),
                    'keterangan' => null,
                    'status' => 'final',
                    'diisi_oleh' => $staffDesaUser->id,
                ]);
            }

            // 4) Jadwal visitasi — status selesai
            $tanggal = fake()->dateTimeBetween('2026-03-01', '2026-05-31');
            $jadwal = JadwalVisitasi::create([
                'desa_id' => $desa->id,
                'periode_id' => $periode->id,
                'tanggal_visitasi' => $tanggal->format('Y-m-d'),
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '12:00',
                'lokasi' => "Kantor {$desa->nama}",
                'petugas_id' => $penilaiUser->id,
                'status' => 'selesai',
                'catatan' => "Visitasi {$desa->nama} telah selesai dilaksanakan.",
                'dibuat_oleh' => $superAdminUser->id,
            ]);

            // 5) Penilaian visitasi — dari indikator_visitasi tabel (per-desa replace global)
            $hasSpecific = IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->where('desa_id', $desa->id)
                ->where('is_active', true)
                ->exists();

            $indikatorList = IndikatorVisitasi::query()
                ->where('periode_id', $periode->id)
                ->where('is_active', true)
                ->where(function ($q) use ($desa, $hasSpecific) {
                    if ($hasSpecific) {
                        $q->where('desa_id', $desa->id);
                    } else {
                        $q->whereNull('desa_id');
                    }
                })
                ->orderBy('urutan')
                ->get();

            foreach ($indikatorList as $indikator) {
                PenilaianVisitasi::create([
                    'jadwal_id' => $jadwal->id,
                    'desa_id' => $desa->id,
                    'periode_id' => $periode->id,
                    'indikator_visitasi' => $indikator->indikator_visitasi,
                    'skor' => fake()->numberBetween(50, 90),
                    'bobot' => $indikator->bobot,
                    'keterangan' => fake()->optional()->sentence(),
                    'dinilai_oleh' => $penilaiUser->id,
                    'tanggal_input' => now(),
                ]);
            }

            // 6) Verifikasi kuesioner — hanya 3 dari 5 diverifikasi
            foreach ($kuesionerList->take(3) as $kues) {
                $status = fake()->randomElement($verifikasiStatuses);
                $catatan = match ($status) {
                    'disetujui' => 'Jawaban sudah sesuai dengan hasil kunjungan lapangan.',
                    'ditolak' => 'Data perlu diperbaiki karena belum sesuai kondisi lapangan.',
                    'perlu_perbaikan' => 'Perlu melengkapi dokumen pendukung untuk validasi.',
                };

                VerifikasiKuesioner::create([
                    'desa_id' => $desa->id,
                    'periode_id' => $periode->id,
                    'kuesioner_id' => $kues->id,
                    'status_verifikasi' => $status,
                    'catatan' => $catatan,
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
