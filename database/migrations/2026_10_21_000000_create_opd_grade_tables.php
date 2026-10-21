<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd_minimal_grade', function (Blueprint $table) {
            $table->id();
            $table->string('unit', 150)->index();
            $table->string('grade_min_max', 100)->nullable();
            $table->string('grade_min', 50)->nullable();
            $table->string('cluster_unit', 150)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('opd_pengajuan_grade', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 50);
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('termin');
            $table->string('nama', 150)->nullable();
            $table->string('site', 100)->nullable();
            $table->string('doh', 50)->nullable();
            $table->string('masa_kerja', 50)->nullable();
            $table->string('pos_saat_ini', 150)->nullable();
            $table->string('pos_tgl_naik', 50)->nullable();
            $table->string('pos_lama_naik', 50)->nullable();
            $table->string('pos_tujuan', 150)->nullable();
            $table->string('grade_saat_ini', 50)->nullable();
            $table->string('grade_tgl_naik', 50)->nullable();
            $table->string('grade_lama_naik', 50)->nullable();
            $table->string('grade_tujuan', 50)->nullable();
            $table->string('vers_saat_ini', 50)->nullable();
            $table->string('vers_seharusnya', 50)->nullable();
            $table->string('mor', 50)->nullable();
            $table->string('atr', 50)->nullable();
            $table->string('sp', 50)->nullable();
            $table->string('remark', 255)->nullable();
            $table->string('remark_warna', 10)->nullable();
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();

            $table->unique(['nrp', 'tahun', 'termin']);
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_pengajuan_grade');
        Schema::dropIfExists('opd_minimal_grade');
    }
};
