<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('alamat');
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('kode_pos', 10)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('kepala_desa', 150)->nullable();
            $table->unsignedInteger('jumlah_penduduk')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['kabupaten', 'kecamatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
