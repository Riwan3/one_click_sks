<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Sinkron ATR meng-upsert per (nrp, tanggal), sama seperti V1; tanpa kunci ini baris akan berganda. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qor_atr_attendance', function (Blueprint $table) {
            $table->unique(['nrp', 'tanggal'], 'uniq_nrp_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('qor_atr_attendance', function (Blueprint $table) {
            $table->dropUnique('uniq_nrp_tanggal');
        });
    }
};
