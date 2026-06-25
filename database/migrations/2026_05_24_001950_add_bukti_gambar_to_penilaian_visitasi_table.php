<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_visitasi', function (Blueprint $table) {
            $table->string('bukti_gambar')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian_visitasi', function (Blueprint $table) {
            $table->dropColumn('bukti_gambar');
        });
    }
};
