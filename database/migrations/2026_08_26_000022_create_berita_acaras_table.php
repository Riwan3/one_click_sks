<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('kronologi');
            $table->foreignId('diketahui_oleh_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_atasan')->nullable();
            $table->string('no_dokumen', 100)->default('PPA-ADRO-F-SHE-24D');
            $table->string('revisi', 20)->default('0');
            $table->date('tgl_efektif')->default('2022-04-07');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};
