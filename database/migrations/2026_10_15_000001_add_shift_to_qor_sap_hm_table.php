<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Samakan dengan V1: impor SAP HM menyimpan shift (dipakai lookup unit per shift). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qor_sap_hm', function (Blueprint $table) {
            $table->string('shift', 5)->nullable()->after('tanggal');
            $table->index(['kode_unit', 'tanggal', 'shift'], 'idx_unit_tanggal_shift');
        });
    }

    public function down(): void
    {
        Schema::table('qor_sap_hm', function (Blueprint $table) {
            $table->dropIndex('idx_unit_tanggal_shift');
            $table->dropColumn('shift');
        });
    }
};
