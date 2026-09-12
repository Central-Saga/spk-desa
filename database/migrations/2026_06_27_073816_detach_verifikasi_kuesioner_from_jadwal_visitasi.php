<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasi_kuesioner', function (Blueprint $table) {
            // Hapus foreign key dulu karena unique constraint dibutuhkannya.
            $table->dropForeign(['jadwal_id']);

            // Baru hapus unique constraint lama berbasis jadwal.
            $table->dropUnique(['jadwal_id', 'kuesioner_id']);

            // Hapus kolom yang tidak lagi relevan.
            $table->dropColumn('jadwal_id');

            // Identitas verifikasi sekarang berbasis desa + periode + kuesioner.
            $table->unique(['desa_id', 'periode_id', 'kuesioner_id']);
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_kuesioner', function (Blueprint $table) {
            $table->dropUnique(['desa_id', 'periode_id', 'kuesioner_id']);

            $table->foreignId('jadwal_id')->after('id')->constrained('jadwal_visitasi')->cascadeOnDelete();

            $table->unique(['jadwal_id', 'kuesioner_id']);
        });
    }
};
