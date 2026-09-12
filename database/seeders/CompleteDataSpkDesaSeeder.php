<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Enums\StatusJawaban;
use App\Enums\StatusPeriode;
use App\Enums\StatusVisitasi;
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDataSpkDesaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Periode 2026 Aktif
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

        $periode->update([
            'nama' => 'Apresiasi Keterbukaan Informasi Publik Desa 2026',
            'status' => StatusPeriode::Aktif->value,
        ]);

        // 2. 5 Desa Resmi Sesuai Gambar Manajemen Desa & Input Skor
        // Urutan dan data persis screenshot client
        $desaConfigs = [
            [
                'nama' => 'Bebandem',
                'kecamatan' => 'Bebandem',
                'kabupaten' => 'karangasem',
                'kepala_desa' => 'I Gede Partadana S.H',
                'jumlah_penduduk' => 10436,
                'alamat' => 'Jl. Raya Bebandem',
                'kode_pos' => '80861',
                'telepon' => '0363-21456',
                'email' => 'desa.bebandem@karangasemkab.go.id',
                'username' => 'bebandem',
                'user_email' => 'desabebandem17@gmail.com',
                'user_name' => 'Desa Bebandem',
                'visitasi_tgl' => '2026-06-25',
                'skor_terisi_count' => 20, // 20/20 = Lengkap
                'skor_visitasi' => [
                    'Penilaian Prestasi' => 80.00,
                    'Penilaian Potensi' => 88.00,
                    'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah' => 90.00,
                    'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya' => 94.00,
                ],
                'base_skor_kuesioner' => 85.00,
            ],
            [
                'nama' => 'Dalung',
                'kecamatan' => 'Kuta Utara',
                'kabupaten' => 'Badung',
                'kepala_desa' => 'I GEDE PUTU ARIF WIRATYA, S.Sos',
                'jumlah_penduduk' => 27714,
                'alamat' => 'Jl. Raya Dalung No. 1',
                'kode_pos' => '80361',
                'telepon' => '0361-9003211',
                'email' => 'dalung@badungkab.go.id',
                'username' => 'dalung',
                'user_email' => 'informasidesadalung@gmail.com',
                'user_name' => 'Desa Dalung',
                'visitasi_tgl' => '2026-07-08',
                'skor_terisi_count' => 20, // 20/20 = Lengkap
                'skor_visitasi' => [
                    'Penilaian Prestasi' => 95.00,
                    'Penilaian Potensi' => 98.00,
                    'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah' => 98.00,
                    'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya' => 100.00,
                ],
                'base_skor_kuesioner' => 96.00,
            ],
            [
                'nama' => 'Kesiman Kertalangu',
                'kecamatan' => 'Denpasar Timur',
                'kabupaten' => 'Bali',
                'kepala_desa' => 'I Made Suena, ST',
                'jumlah_penduduk' => 18969,
                'alamat' => 'Jl. Bypass Ngurah Rai No. 234',
                'kode_pos' => '80237',
                'telepon' => '0361-462153',
                'email' => 'kesimankertalangu@denpasarkota.go.id',
                'username' => 'kesimankertalangu',
                'user_email' => 'kesimankertalangu@gmail.com',
                'user_name' => 'Kesiman Kertalangu',
                'visitasi_tgl' => '2026-07-04',
                'skor_terisi_count' => 14, // 14/20 = 70%
                'skor_visitasi' => [
                    'Penilaian Prestasi' => 100.00,
                    'Penilaian Potensi' => 98.00,
                    'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah' => 97.00,
                    'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya' => 100.00,
                ],
                'base_skor_kuesioner' => 98.00,
            ],
            [
                'nama' => 'Pejarakan',
                'kecamatan' => 'Gerokgak',
                'kabupaten' => 'Buleleng',
                'kepala_desa' => 'I Made Astawa',
                'jumlah_penduduk' => 9264,
                'alamat' => 'Jl. Singaraja - Gilimanuk',
                'kode_pos' => '81155',
                'telepon' => '0362-93456',
                'email' => 'pejarakan@bulelengkab.go.id',
                'username' => 'pejarakan',
                'user_email' => 'pejarakangerokgakcam@gmail.com',
                'user_name' => 'Desa Pejarakan',
                'visitasi_tgl' => '2026-06-23',
                'skor_terisi_count' => 19, // 19/20 = 95%
                'skor_visitasi' => [
                    'Penilaian Prestasi' => 94.00,
                    'Penilaian Potensi' => 95.00,
                    'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah' => 97.00,
                    'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya' => 99.00,
                ],
                'base_skor_kuesioner' => 94.00,
            ],
            [
                'nama' => 'Peliatan',
                'kecamatan' => 'Ubud',
                'kabupaten' => 'Gianyar',
                'kepala_desa' => 'I MADE DWI SUTARYANTHA',
                'jumlah_penduduk' => 8709,
                'alamat' => 'Jl. Raya Peliatan No. 1',
                'kode_pos' => '80571',
                'telepon' => '0361-975321',
                'email' => 'peliatan@gianyarkab.go.id',
                'username' => 'peliatan',
                'user_email' => 'kantorpeliatan@gmail.com',
                'user_name' => 'Desa Peliatan',
                'visitasi_tgl' => '2026-06-24',
                'skor_terisi_count' => 16, // 16/20 = 80%
                'skor_visitasi' => [
                    'Penilaian Prestasi' => 96.00,
                    'Penilaian Potensi' => 99.00,
                    'Penilaian Kesiapan Desa Mengimplementasikan Kebijakan Pimpinan Daerah' => 98.00,
                    'Penilaian Inovasi, Aplikasi/Digitalisasi Dan Manfaatnya' => 100.00,
                ],
                'base_skor_kuesioner' => 96.00,
            ],
        ];

        // Petugas Penilai
        $petugas = User::where('username', 'penilai')->first();
        if (! $petugas) {
            $petugas = User::create([
                'name' => 'Staff Penilaian Komisi Informasi',
                'username' => 'penilai',
                'email' => 'staffkomisiinformasi@gmail.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $petugas->syncRoles([RoleSlug::StaffPenilaian->value]);
        }

        $superadmin = User::where('username', 'superadmin')->first();

        // 3. 20 Pertanyaan Kuesioner
        $kuesionerList = Kuesioner::where('periode_id', $periode->id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $activeDesaIds = [];

        foreach ($desaConfigs as $cfg) {
            $desa = Desa::updateOrCreate(
                ['nama' => $cfg['nama']],
                [
                    'kecamatan' => $cfg['kecamatan'],
                    'kabupaten' => $cfg['kabupaten'],
                    'kepala_desa' => $cfg['kepala_desa'],
                    'jumlah_penduduk' => $cfg['jumlah_penduduk'],
                    'alamat' => $cfg['alamat'],
                    'kode_pos' => $cfg['kode_pos'],
                    'telepon' => $cfg['telepon'],
                    'email' => $cfg['email'],
                    'is_active' => true,
                ]
            );

            $activeDesaIds[] = $desa->id;

            // 1 User Pengguna Aktif per Desa
            $userDesa = User::updateOrCreate(
                ['username' => $cfg['username']],
                [
                    'name' => $cfg['user_name'],
                    'email' => $cfg['user_email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'desa_id' => $desa->id,
                ]
            );
            $userDesa->syncRoles([RoleSlug::StaffAdminDesa->value]);

            // Seed Jawaban Kuesioner dengan jumlah skor terisi persis screenshot client
            foreach ($kuesionerList as $idx => $q) {
                $hasScore = ($idx < $cfg['skor_terisi_count']);
                JawabanKuesioner::updateOrCreate(
                    [
                        'desa_id' => $desa->id,
                        'kuesioner_id' => $q->id,
                        'periode_id' => $periode->id,
                    ],
                    [
                        'jawaban' => 'Telah diumumkan dan tersedia di portal layanan informasi serta kantor desa.',
                        'status' => $hasScore ? StatusJawaban::Final->value : StatusJawaban::Draft->value,
                        'status_jawaban' => 'iya',
                        'skor' => $hasScore ? $cfg['base_skor_kuesioner'] : null,
                        'keterangan' => $hasScore ? 'Terverifikasi lengkap' : null,
                        'diisi_oleh' => $userDesa->id,
                    ]
                );
            }

            // Seed Jadwal Visitasi (Status: Selesai, tanggal persis Gambar 1)
            $jadwal = JadwalVisitasi::updateOrCreate(
                [
                    'desa_id' => $desa->id,
                    'periode_id' => $periode->id,
                ],
                [
                    'tanggal_visitasi' => $cfg['visitasi_tgl'],
                    'waktu_mulai' => '09:00',
                    'waktu_selesai' => '12:00',
                    'lokasi' => 'Kantor Desa ' . $desa->nama,
                    'petugas_id' => $petugas->id,
                    'status' => StatusVisitasi::Selesai->value,
                    'catatan' => 'Visitasi lapangan terlaksana dengan lancar',
                    'dibuat_oleh' => $superadmin?->id ?? $petugas->id,
                ]
            );

            // Seed Penilaian Visitasi (4/4 Indikator Dinilai, Status Selesai)
            foreach ($cfg['skor_visitasi'] as $indikatorNama => $skorVal) {
                PenilaianVisitasi::updateOrCreate(
                    [
                        'jadwal_id' => $jadwal->id,
                        'desa_id' => $desa->id,
                        'periode_id' => $periode->id,
                        'indikator_visitasi' => $indikatorNama,
                    ],
                    [
                        'skor' => $skorVal,
                        'bobot' => 25.00,
                        'keterangan' => 'Hasil verifikasi lapangan terpenuhi.',
                        'dinilai_oleh' => $petugas->id,
                        'tanggal_input' => $cfg['visitasi_tgl'] . ' 11:30:00',
                    ]
                );
            }
        }

        // Hapus desa dummy bawaan yang bukan bagian dari 5 desa resmi
        $desaLama = Desa::whereNotIn('id', $activeDesaIds)->get();
        foreach ($desaLama as $dl) {
            User::where('desa_id', $dl->id)->update(['desa_id' => null]);
            JawabanKuesioner::where('desa_id', $dl->id)->delete();
            JadwalVisitasi::where('desa_id', $dl->id)->delete();
            PenilaianVisitasi::where('desa_id', $dl->id)->delete();
            $dl->delete();
        }

        // Hitung Nilai Akhir
        if ($superadmin) {
            app(PerhitunganNilaiService::class)->hitungSemuaDesa($periode, $superadmin);
        }
    }
}
