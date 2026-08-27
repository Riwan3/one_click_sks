<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemulangan_karyawans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('doc_rank')->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nrp', 20)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->string('lokasi', 100)->nullable();
            $table->date('tanggal')->nullable();
            $table->text('indikasi_fatigue')->nullable();
            $table->boolean('jam_tidur_kurang_4jam')->default(false);
            $table->boolean('fatigue_berat')->default(false);
            $table->text('deskripsi_penyebab')->nullable();
            $table->text('alamat_pengantaran')->nullable();
            $table->string('penanggungjawab_pengantaran', 150)->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_pengawas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nrp');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemulangan_karyawans');
    }
};
