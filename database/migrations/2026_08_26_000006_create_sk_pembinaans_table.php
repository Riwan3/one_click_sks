<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_pembinaans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100);
            $table->string('perusahaan', 255)->default('PT. PUTRA PERKASA ABADI');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('tanggal_pelanggaran')->nullable();
            $table->time('jam_pelanggaran')->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->string('spdk_ref', 100)->nullable();
            $table->string('dikeluarkan_di', 100)->nullable();
            $table->date('dikeluarkan_tanggal')->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_atasan')->nullable();
            $table->foreignId('pengawas_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('jenis_pelanggaran')->nullable();
            $table->unsignedInteger('lubang')->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('dikeluarkan_tanggal');
            $table->index('berlaku_sampai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_pembinaans');
    }
};
