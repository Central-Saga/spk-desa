<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_visitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal_visitasi')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->string('indikator_visitasi', 150);
            $table->decimal('skor', 5, 2)->default(0);
            $table->decimal('bobot', 5, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('dinilai_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamp('tanggal_input')->useCurrent();
            $table->timestamps();

            $table->index(['periode_id', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_visitasi');
    }
};
