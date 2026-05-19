<?php

namespace Database\Seeders;

use App\Models\Desa;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    public function run(): void
    {
        Desa::firstOrCreate(
            ['nama' => 'Desa Bedugul'],
            [
                'alamat' => 'Jl. Raya Bedugul No. 1',
                'kecamatan' => 'Baturiti',
                'kabupaten' => 'Tabanan',
                'kode_pos' => '82191',
                'telepon' => '0368-2033123',
                'email' => 'bedugul@desa.id',
                'kepala_desa' => 'I Wayan Bedugul',
                'jumlah_penduduk' => 4500,
                'is_active' => true,
            ]
        );

        Desa::firstOrCreate(
            ['nama' => 'Desa Penglipuran'],
            [
                'alamat' => 'Jl. Penglipuran, Kubu',
                'kecamatan' => 'Bangli',
                'kabupaten' => 'Bangli',
                'kode_pos' => '80661',
                'telepon' => '0366-91537',
                'email' => 'penglipuran@desa.id',
                'kepala_desa' => 'I Made Penglipuran',
                'jumlah_penduduk' => 1300,
                'is_active' => true,
            ]
        );

        Desa::firstOrCreate(
            ['nama' => 'Desa Trunyan'],
            [
                'alamat' => 'Tepi Danau Batur',
                'kecamatan' => 'Kintamani',
                'kabupaten' => 'Bangli',
                'kode_pos' => '80652',
                'kepala_desa' => 'I Ketut Trunyan',
                'jumlah_penduduk' => 800,
                'is_active' => true,
            ]
        );
    }
}
