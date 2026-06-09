<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom desa_id sudah ditambahkan migrasi sebelumnya.
        // Migrasi ini menyesuaikan keunikan kode menjadi per periode + desa.
        Schema::table('indikator_visitasi', function (Blueprint $table) {
            // Buat indeks baru lebih dulu agar foreign key periode_id tetap terpenuhi,
            // baru kemudian lepas unique lama.
            $table->unique(['periode_id', 'desa_id', 'kode'], 'indikator_visitasi_periode_desa_kode_unique');
            $table->index(['periode_id', 'desa_id', 'is_active'], 'indikator_visitasi_periode_desa_aktif_idx');
            $table->dropUnique('indikator_visitasi_periode_id_kode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('indikator_visitasi', function (Blueprint $table) {
            $table->unique(['periode_id', 'kode'], 'indikator_visitasi_periode_id_kode_unique');
            $table->dropUnique('indikator_visitasi_periode_desa_kode_unique');
            $table->dropIndex('indikator_visitasi_periode_desa_aktif_idx');
        });
    }
};
