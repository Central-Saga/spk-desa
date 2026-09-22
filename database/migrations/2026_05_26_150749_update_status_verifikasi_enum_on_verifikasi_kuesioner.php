<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasi_kuesioner', function (Blueprint $table) {
            $table->enum('status_verifikasi', ['disetujui', 'ditolak', 'perlu_perbaikan'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_kuesioner', function (Blueprint $table) {
            $table->enum('status_verifikasi', ['ya', 'tidak'])->change();
        });
    }
};
