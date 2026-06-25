<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator_visitasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->string('kategori', 100);
            $table->string('kode', 50);
            $table->text('indikator_visitasi');
            $table->decimal('bobot', 5, 2);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['periode_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator_visitasi');
    }
};
