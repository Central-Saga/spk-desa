<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuesioner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->string('kategori', 100);
            $table->string('kode_indikator', 50);
            $table->text('pertanyaan');
            $table->decimal('bobot_indikator', 5, 2);
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['periode_id', 'kode_indikator']);
            $table->index(['periode_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuesioner');
    }
};
