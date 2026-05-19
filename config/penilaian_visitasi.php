<?php

/*
 * Indikator visitasi default — dipakai semua periode, total bobot harus 100.
 * Ketika diubah, hanya berdampak pada periode baru atau jadwal yang belum dinilai.
 */

return [
    'indikator' => [
        [
            'kode' => 'V-OBSV-01',
            'nama' => 'Kondisi Fisik Kantor Desa',
            'deskripsi' => 'Kelayakan dan kebersihan kantor desa sebagai pusat pelayanan publik.',
            'bobot' => 20,
        ],
        [
            'kode' => 'V-OBSV-02',
            'nama' => 'Sarana Layanan Informasi',
            'deskripsi' => 'Keberadaan papan informasi, layar publik, atau media transparansi anggaran.',
            'bobot' => 25,
        ],
        [
            'kode' => 'V-OBSV-03',
            'nama' => 'Wawancara Warga & Aparatur',
            'deskripsi' => 'Tingkat kepuasan warga atas keterbukaan informasi desa.',
            'bobot' => 25,
        ],
        [
            'kode' => 'V-OBSV-04',
            'nama' => 'Dokumentasi Musyawarah Desa',
            'deskripsi' => 'Bukti notulen, daftar hadir, dan dokumentasi musyawarah desa.',
            'bobot' => 15,
        ],
        [
            'kode' => 'V-OBSV-05',
            'nama' => 'Inovasi Pelayanan',
            'deskripsi' => 'Inovasi digital atau pelayanan publik berbasis transparansi informasi.',
            'bobot' => 15,
        ],
    ],
];
