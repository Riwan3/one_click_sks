<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_peringatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nomor_sp', 100)->nullable();
            $table->string('kode_pelanggaran', 50)->nullable();
            $table->string('jenis_pembinaan', 100);
            $table->text('detail_pelanggaran')->nullable();
            $table->date('tanggal_kejadian')->nullable();
            $table->time('jam_kejadian')->nullable();
            $table->string('tempat_kejadian', 255)->nullable();
            $table->boolean('pelanggaran_absen_terlambat')->default(false);
            $table->boolean('pelanggaran_perilaku')->default(false);
            $table->boolean('pelanggaran_ceroboh_lalai')->default(false);
            $table->boolean('pelanggaran_hasil_kerja')->default(false);
            $table->boolean('pelanggaran_menolak_perintah')->default(false);
            $table->boolean('pelanggaran_peraturan')->default(false);
            $table->string('peringatan_sebelumnya_status', 20)->nullable();
            $table->string('peringatan_sebelumnya_jenis', 100)->nullable();
            $table->text('peringatan_sebelumnya_mengenai')->nullable();
            $table->unsignedInteger('sanksi_masa_berlaku')->default(6);
            $table->string('dikeluarkan_di', 100)->nullable();
            $table->date('dikeluarkan_tanggal')->nullable();
            $table->text('catatan_karyawan')->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_atasan')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pengawas_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('koreksi_alpa', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('dikeluarkan_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_peringatans');
    }
};
