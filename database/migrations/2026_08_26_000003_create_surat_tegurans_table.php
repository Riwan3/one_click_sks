<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_tegurans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('doc_rank')->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nrp', 20)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->date('tanggal_kejadian')->nullable();
            $table->string('tempat_kejadian', 150)->nullable();
            $table->boolean('jenis_absen')->default(false);
            $table->boolean('jenis_perilaku')->default(false);
            $table->boolean('jenis_ceroboh')->default(false);
            $table->boolean('jenis_hasil_kerja')->default(false);
            $table->boolean('jenis_menolak_perintah')->default(false);
            $table->boolean('jenis_pelanggaran_peraturan')->default(false);
            $table->text('penjelasan_singkat')->nullable();
            $table->date('kejadian_tanggal')->nullable();
            $table->time('kejadian_jam')->nullable();
            $table->string('kejadian_lokasi', 150)->nullable();
            $table->string('dikeluarkan_di', 100)->nullable();
            $table->date('dikeluarkan_tanggal')->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_atasan')->nullable();
            $table->longText('ttd_atasan_atasan')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nrp');
            $table->index('tanggal_kejadian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_tegurans');
    }
};
