<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pakta_integritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nomor_surat', 100)->nullable();
            $table->date('tanggal');
            $table->string('tempat', 100)->default('Tanjung');
            $table->text('pernyataan');
            $table->string('atasan_nama', 150)->nullable();
            $table->string('mengetahui_nama', 150)->nullable();
            $table->string('cc', 255)->default('Bapak Fachrul Latuconsina');
            $table->string('arsip', 255)->default('Dokumen Control MPE');
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_atasan')->nullable();
            $table->longText('ttd_mengetahui')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pakta_integritas');
    }
};
