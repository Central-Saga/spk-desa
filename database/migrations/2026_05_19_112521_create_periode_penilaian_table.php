<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->string('nama', 150);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tahun');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_penilaian');
    }
};
