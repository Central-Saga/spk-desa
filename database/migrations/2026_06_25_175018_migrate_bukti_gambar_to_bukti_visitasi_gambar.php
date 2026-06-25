<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('penilaian_visitasi')
            ->whereNotNull('bukti_gambar')
            ->where('bukti_gambar', '!=', '')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('bukti_visitasi_gambar')->insert([
                    'penilaian_visitasi_id' => $row->id,
                    'path' => $row->bukti_gambar,
                    'urutan' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('bukti_visitasi_gambar')->truncate();
    }
};
