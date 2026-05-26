<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE verifikasi_kuesioner MODIFY COLUMN status_verifikasi ENUM('disetujui', 'ditolak', 'perlu_perbaikan') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE verifikasi_kuesioner MODIFY COLUMN status_verifikasi ENUM('ya', 'tidak') NOT NULL");
    }
};
