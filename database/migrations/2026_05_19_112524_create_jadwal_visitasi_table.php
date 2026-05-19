<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_visitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->date('tanggal_visitasi');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi', 255);
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['terjadwal', 'berlangsung', 'selesai', 'dibatalkan'])->default('terjadwal');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['periode_id', 'status']);
            $table->index(['petugas_id', 'tanggal_visitasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_visitasi');
    }
};
