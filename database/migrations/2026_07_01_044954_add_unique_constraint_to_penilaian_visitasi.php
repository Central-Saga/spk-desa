<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penilaian_visitasi', function (Blueprint $table) {
            $table->unique(['jadwal_id', 'indikator_visitasi'], 'penilaian_visitasi_jadwal_indikator_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian_visitasi', function (Blueprint $table) {
            $table->dropUnique('penilaian_visitasi_jadwal_indikator_unique');
        });
    }
};
