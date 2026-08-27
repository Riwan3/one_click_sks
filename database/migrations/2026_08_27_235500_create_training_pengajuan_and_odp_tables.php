<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('training_pengajuan')) {
            Schema::create('training_pengajuan', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_pengajuan')->nullable();
                $table->string('type_unit')->nullable();
                $table->string('tahapan')->default('Pengajuan Baru');
                
                $table->string('diajukan_nama')->nullable();
                $table->string('diajukan_nik')->nullable();
                $table->dateTime('diajukan_signed_at')->nullable();
                $table->longText('diajukan_signature')->nullable();

                $table->string('diperiksa_nama')->nullable();
                $table->string('diperiksa_nik')->nullable();
                $table->dateTime('diperiksa_signed_at')->nullable();
                $table->longText('diperiksa_signature')->nullable();

                $table->string('disetujui_nama')->nullable();
                $table->string('disetujui_nik')->nullable();
                $table->dateTime('disetujui_signed_at')->nullable();
                $table->longText('disetujui_signature')->nullable();

                $table->string('diterima_nama')->nullable();
                $table->string('diterima_nik')->nullable();
                $table->dateTime('diterima_signed_at')->nullable();
                $table->longText('diterima_signature')->nullable();

                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('training_pengajuan_peserta')) {
            Schema::create('training_pengajuan_peserta', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengajuan_id')->constrained('training_pengajuan')->onDelete('cascade');
                $table->string('nama')->nullable();
                $table->string('nrp')->nullable();
                $table->string('nik')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('jabatan')->nullable();
                $table->string('perusahaan')->default('PUTRA PERKASA ABADI / PRODUKSI');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_karyawan')) {
            Schema::create('odp_karyawan', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('nrp');
                $table->string('jabatan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_pelatihan')) {
            Schema::create('odp_pelatihan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('odp_karyawan')->onDelete('cascade');
                $table->string('nama_pelatihan');
                $table->date('tanggal')->nullable();
                $table->string('penyelenggara')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_sertifikat')) {
            Schema::create('odp_sertifikat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('odp_karyawan')->onDelete('cascade');
                $table->string('nama_sertifikat')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('penerbit')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_type', 10)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_cv')) {
            Schema::create('odp_cv', function (Blueprint $table) {
                $table->id();
                $table->foreignId('karyawan_id')->constrained('odp_karyawan')->onDelete('cascade');
                $table->string('file_path')->nullable();
                $table->string('file_type', 10)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('odp_cv');
        Schema::dropIfExists('odp_sertifikat');
        Schema::dropIfExists('odp_pelatihan');
        Schema::dropIfExists('odp_karyawan');
        Schema::dropIfExists('training_pengajuan_peserta');
        Schema::dropIfExists('training_pengajuan');
    }
};
