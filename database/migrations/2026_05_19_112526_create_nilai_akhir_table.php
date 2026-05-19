<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->decimal('nilai_kuesioner', 5, 2)->default(0);
            $table->decimal('nilai_visitasi', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->unsignedInteger('peringkat')->nullable();
            $table->timestamp('dihitung_pada')->nullable();
            $table->foreignId('dihitung_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['desa_id', 'periode_id']);
            $table->index(['periode_id', 'peringkat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
    }
};
