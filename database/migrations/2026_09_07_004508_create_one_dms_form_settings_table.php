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
        Schema::create('one_dms_form_settings', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no')->default('PPA-ADRO-F-PRO-32B');
            $table->string('doc_revisi')->default('0');
            $table->string('doc_tgl_efektif')->default('01 Mei 2020');
            $table->string('doc_halaman')->default('1');
            $table->string('tally_title')->default('Monitoring IN CAR CAMERA harian PT PUTRA PERKASA ABADI WARA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_dms_form_settings');
    }
};
