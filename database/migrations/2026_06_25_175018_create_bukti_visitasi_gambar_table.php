<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bukti_visitasi_gambar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_visitasi_id')->constrained('penilaian_visitasi')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['penilaian_visitasi_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_visitasi_gambar');
    }
};
