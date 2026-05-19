<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_kuesioner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->foreignId('kuesioner_id')->constrained('kuesioner')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->decimal('skor', 5, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignId('diisi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['desa_id', 'kuesioner_id', 'periode_id']);
            $table->index(['periode_id', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_kuesioner');
    }
};
