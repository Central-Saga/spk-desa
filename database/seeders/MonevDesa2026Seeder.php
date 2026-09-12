<?php

namespace Database\Seeders;

use App\Enums\StatusPeriode;
use App\Models\Desa;
use App\Models\IndikatorVisitasi;
use App\Models\Kuesioner;
use App\Models\PeriodePenilaian;
use Illuminate\Database\Seeder;

class MonevDesa2026Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Periode 2026 aktif
        $periode = PeriodePenilaian::firstOrCreate(
            ['tahun' => 2026],
            [
                'nama' => 'Apresiasi Keterbukaan Informasi Publik Desa 2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'status' => StatusPeriode::Aktif->value,
                'keterangan' => 'Monev Keterbukaan Informasi Publik Desa Tahun 2026 - Komisi Informasi Provinsi Bali',
            ]
        );

        if ($periode->status !== StatusPeriode::Aktif) {
            $periode->update(['status' => StatusPeriode::Aktif->value]);
        }

        // 2. 5 Desa Resmi
        $desaData = [
            [
                'nama' => 'Desa Kesiman Kertalangu',
                'kecamatan' => 'Denpasar Timur',
                'kabupaten' => 'Denpasar',
                'alamat' => 'Jl. Bypass Ngurah Rai No. 234',
                'kode_pos' => '80237',
                'telepon' => '0361-462153',
                'email' => 'kesimankertalangu@denpasarkota.go.id',
                'kepala_desa' => 'I Made Suena, ST',
                'jumlah_penduduk' => 12500,
                'is_active' => true,
            ],
            [
                'nama' => 'Desa Bebandem',
                'kecamatan' => 'Bebandem',
                'kabupaten' => 'Karangasem',
                'alamat' => 'Jl. Raya Bebandem',
                'kode_pos' => '80861',
                'telepon' => '0363-21456',
                'email' => 'desa.bebandem@karangasemkab.go.id',
                'kepala_desa' => 'I Gede Partadana, S.H.',
                'jumlah_penduduk' => 9800,
                'is_active' => true,
            ],
            [
                'nama' => 'Desa Peliatan',
                'kecamatan' => 'Ubud',
                'kabupaten' => 'Gianyar',
                'alamat' => 'Jl. Raya Peliatan No. 1',
                'kode_pos' => '80571',
                'telepon' => '0361-975321',
                'email' => 'peliatan@gianyarkab.go.id',
                'kepala_desa' => 'I Made Dwi Sutaryantha',
                'jumlah_penduduk' => 8700,
                'is_active' => true,
            ],
            [
                'nama' => 'Desa Pejarakan',
                'kecamatan' => 'Gerokgak',
                'kabupaten' => 'Buleleng',
                'alamat' => 'Jl. Singaraja - Gilimanuk',
                'kode_pos' => '81155',
                'telepon' => '0362-93456',
                'email' => 'pejarakan@bulelengkab.go.id',
                'kepala_desa' => 'I Made Astawa',
                'jumlah_penduduk' => 7400,
                'is_active' => true,
            ],
            [
                'nama' => 'Desa Dalung',
                'kecamatan' => 'Kuta Utara',
                'kabupaten' => 'Badung',
                'alamat' => 'Jl. Raya Dalung No. 1',
                'kode_pos' => '80361',
                'telepon' => '0361-9003211',
                'email' => 'dalung@badungkab.go.id',
                'kepala_desa' => 'I Gede Putu Arif Wiratya, S.Sos.',
                'jumlah_penduduk' => 18200,
                'is_active' => true,
            ],
        ];

        foreach ($desaData as $d) {
            Desa::updateOrCreate(['nama' => $d['nama']], $d);
        }

        // 3. 20 Pertanyaan Kuesioner Monev Desa 2026 (Bobot: 5.00 masing-masing, Total: 100)
        $kuesionerData = [
            [
                'kode_indikator' => 'Q01',
                'kategori' => 'Profil dan PPID Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan profil desa secara lengkap, meliputi alamat desa, visi misi, tugas dan fungsi, struktur organisasi, profil kepala desa, profil perangkat desa, serta profil desa?',
                'bobot_indikator' => 5.00,
                'urutan' => 1,
            ],
            [
                'kode_indikator' => 'Q02',
                'kategori' => 'Profil dan PPID Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menetapkan dan mengumumkan Pejabat Pengelola Informasi dan Dokumentasi atau PPID Desa kepada masyarakat?',
                'bobot_indikator' => 5.00,
                'urutan' => 2,
            ],
            [
                'kode_indikator' => 'Q03',
                'kategori' => 'Profil dan PPID Desa',
                'pertanyaan' => 'Apakah informasi mengenai visi misi, tugas dan fungsi, serta struktur organisasi PPID Desa telah tersedia dan dapat diakses oleh masyarakat?',
                'bobot_indikator' => 5.00,
                'urutan' => 3,
            ],
            [
                'kode_indikator' => 'Q04',
                'kategori' => 'Program Strategis Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan program atau kegiatan strategis yang sedang dijalankan secara berkala?',
                'bobot_indikator' => 5.00,
                'urutan' => 4,
            ],
            [
                'kode_indikator' => 'Q05',
                'kategori' => 'Program Strategis Desa',
                'pertanyaan' => 'Apakah informasi program atau kegiatan desa telah memuat nama kegiatan, jadwal pelaksanaan, penanggung jawab, sumber anggaran, dan besaran anggaran?',
                'bobot_indikator' => 5.00,
                'urutan' => 5,
            ],
            [
                'kode_indikator' => 'Q06',
                'kategori' => 'Program Strategis Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan dokumen perencanaan desa seperti RPJMDes, RKP Desa Tahun 2026, dan APBDes Tahun 2026?',
                'bobot_indikator' => 5.00,
                'urutan' => 6,
            ],
            [
                'kode_indikator' => 'Q07',
                'kategori' => 'Program Strategis Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah melaksanakan Musyawarah Desa, Musyawarah Pembangunan Desa, dan Musrenbang Desa secara tepat waktu?',
                'bobot_indikator' => 5.00,
                'urutan' => 7,
            ],
            [
                'kode_indikator' => 'Q08',
                'kategori' => 'Laporan Kinerja dan Keuangan',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan laporan kinerja pemerintah desa, seperti LPPD akhir tahun anggaran dan LPPD akhir masa jabatan?',
                'bobot_indikator' => 5.00,
                'urutan' => 8,
            ],
            [
                'kode_indikator' => 'Q09',
                'kategori' => 'Laporan Kinerja dan Keuangan',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan laporan keuangan desa yang memuat realisasi APBDes, realisasi kegiatan, kegiatan yang belum selesai, serta sisa anggaran?',
                'bobot_indikator' => 5.00,
                'urutan' => 9,
            ],
            [
                'kode_indikator' => 'Q10',
                'kategori' => 'Laporan Kinerja dan Keuangan',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan informasi mengenai pengadaan barang dan jasa kepada masyarakat?',
                'bobot_indikator' => 5.00,
                'urutan' => 10,
            ],
            [
                'kode_indikator' => 'Q11',
                'kategori' => 'Layanan Informasi Publik',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menyediakan dan mengumumkan Daftar Informasi Publik atau DIP Desa?',
                'bobot_indikator' => 5.00,
                'urutan' => 11,
            ],
            [
                'kode_indikator' => 'Q12',
                'kategori' => 'Layanan Informasi Publik',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan tata cara permohonan informasi publik, tata cara pengajuan keberatan, dan tata cara penyelesaian sengketa informasi?',
                'bobot_indikator' => 5.00,
                'urutan' => 12,
            ],
            [
                'kode_indikator' => 'Q13',
                'kategori' => 'Layanan Informasi Publik',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menyediakan formulir permohonan informasi publik dan formulir keberatan, baik secara online maupun di meja layanan informasi?',
                'bobot_indikator' => 5.00,
                'urutan' => 13,
            ],
            [
                'kode_indikator' => 'Q14',
                'kategori' => 'Layanan Informasi Publik',
                'pertanyaan' => 'Apakah Pemerintah Desa telah membuat dan menyampaikan laporan evaluasi layanan informasi publik kepada Musyawarah Desa, Komisi Informasi, dan Pemerintah Daerah?',
                'bobot_indikator' => 5.00,
                'urutan' => 14,
            ],
            [
                'kode_indikator' => 'Q15',
                'kategori' => 'Informasi Publik dan BUM Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menyediakan informasi publik desa seperti keputusan BPD, perjanjian dengan pihak ketiga, surat menyurat pimpinan, data aset desa, berita acara musyawarah, serta dokumen BUM Desa?',
                'bobot_indikator' => 5.00,
                'urutan' => 15,
            ],
            [
                'kode_indikator' => 'Q16',
                'kategori' => 'Informasi Publik dan BUM Desa',
                'pertanyaan' => 'Apakah Pemerintah Desa telah mengumumkan informasi terkait alokasi anggaran penyertaan modal BUM Desa, BLT Dana Desa, dan daftar KPM penerima BLT DD?',
                'bobot_indikator' => 5.00,
                'urutan' => 16,
            ],
            [
                'kode_indikator' => 'Q17',
                'kategori' => 'Sistem Informasi Kependudukan',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menggunakan Sistem Informasi Kependudukan atau SIK untuk perekaman data penduduk, integrasi data kependudukan, pengarsipan dokumen, dan pengolahan keuangan?',
                'bobot_indikator' => 5.00,
                'urutan' => 17,
            ],
            [
                'kode_indikator' => 'Q18',
                'kategori' => 'Komitmen Keterbukaan Informasi',
                'pertanyaan' => 'Apakah Pemerintah Desa telah memiliki komitmen keterbukaan informasi melalui peraturan desa, SOP layanan informasi publik, anggaran layanan informasi, website desa, dan aplikasi layanan informasi desa?',
                'bobot_indikator' => 5.00,
                'urutan' => 18,
            ],
            [
                'kode_indikator' => 'Q19',
                'kategori' => 'Komitmen Keterbukaan Informasi',
                'pertanyaan' => 'Apakah Pemerintah Desa memiliki sumber daya manusia yang mendukung pelayanan informasi publik, seperti petugas layanan informasi dan petugas pengelola kearsipan?',
                'bobot_indikator' => 5.00,
                'urutan' => 19,
            ],
            [
                'kode_indikator' => 'Q20',
                'kategori' => 'Partisipasi dan Akses Informasi',
                'pertanyaan' => 'Apakah Pemerintah Desa telah menyediakan akses partisipasi dan layanan informasi bagi masyarakat, seperti forum aspirasi, sarana pengaduan, meja layanan informasi, fasilitas ramah disabilitas, papan proyek, dan tanda khusus pada rumah penerima bantuan sosial?',
                'bobot_indikator' => 5.00,
                'urutan' => 20,
            ],
        ];

        // Hapus kuesioner lama periode 2026
        Kuesioner::where('periode_id', $periode->id)->forceDelete();

        foreach ($kuesionerData as $item) {
            $item['periode_id'] = $periode->id;
            $item['is_active'] = true;
            Kuesioner::create($item);
        }

        // 4. 4 Indikator Visitasi Desa 2026 (Bobot: 25.00 masing-masing, Total: 100)
        $visitasiData = [
            [
                'kode' => 'PR-01',
                'kategori' => 'Prestasi',
                'indikator_visitasi' => 'Penilaian Prestasi',
                'deskripsi' => 'A. LOMBA DESA (Juara Nasional, Juara Provinsi, Juara Kabupaten); B. DESA ANTI KORUPSI (WBK/WBBM, Desa Cantik, Desa Wisata, Desa Sadarkum, Desa Ramah Anak, DLL); C. PENGHARGAAN PERBEKEL/PERANGKAT (Paralegal, Pengabdian Lingkungan, DLL)',
                'bobot' => 25.00,
                'urutan' => 1,
            ],
            [
                'kode' => 'PO-01',
                'kategori' => 'Potensi',
                'indikator_visitasi' => 'Penilaian Potensi',
                'deskripsi' => 'A. Ada peningkatan pendapatan masyarakat melalui UMKM; B. Ada pengembangan dan manfaat sebagai desa wisata; C. Ada sumber PAD yang baru',
                'bobot' => 25.00,
                'urutan' => 2,
            ],
            [
                'kode' => 'KE-01',
                'kategori' => 'Kesiapan',
                'indikator_visitasi' => 'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah',
                'deskripsi' => 'A. Implementasi kebijakan pengelolaan lingkungan yang baik (Pengelolaan sampah berbasis sumber, Pembatasan sampah plastik); B. Kebijakan pelestarian budaya Bali (Pelaksanaan Bulan Bahasa Bali, Pakaian Adat Bali, Pemanfaatan buah lokal, Pengalokasian bantuan dana desa untuk seni budaya dan keagamaan); C. Penanganan warga yang mengalami stunting',
                'bobot' => 25.00,
                'urutan' => 3,
            ],
            [
                'kode' => 'IN-01',
                'kategori' => 'Inovasi',
                'indikator_visitasi' => 'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya',
                'deskripsi' => 'A. Inovasi pelayanan publik dan manfaatnya bagi masyarakat; B. Jenis aplikasi digital dan manfaatnya; C. Penanganan ketika ada warga meminta informasi; D. Penganggaran keterbukaan informasi desa',
                'bobot' => 25.00,
                'urutan' => 4,
            ],
        ];

        // Hapus indikator visitasi lama periode 2026
        IndikatorVisitasi::where('periode_id', $periode->id)->delete();

        foreach ($visitasiData as $item) {
            $item['periode_id'] = $periode->id;
            $item['desa_id'] = null; // Global untuk seluruh desa
            $item['is_active'] = true;
            IndikatorVisitasi::create($item);
        }
    }
}
