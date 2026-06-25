<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indikator_visitasi', function (Blueprint $table) {
            $table->foreignId('desa_id')
                ->nullable()
                ->after('periode_id')
                ->constrained('desa')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('indikator_visitasi', function (Blueprint $table) {
            $table->dropForeign(['desa_id']);
            $table->dropColumn('desa_id');
        });
    }
};
