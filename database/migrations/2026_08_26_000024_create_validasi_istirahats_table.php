<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validasi_istirahats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('doc_rank')->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nrp', 20)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->text('alasan_kurang_tidur')->nullable();
            $table->text('alasan_kurang_tidur_berulang')->nullable();
            $table->text('kendala_tidur')->nullable();
            $table->enum('tinggal_di', ['Mess', 'Non Mess'])->default('Non Mess');
            $table->string('nama_mess', 255)->nullable();
            $table->text('alamat_non_mess')->nullable();
            $table->string('status_perkawinan', 50)->nullable();
            $table->string('jumlah_anak_serumah', 100)->nullable();
            $table->string('anak_usia_kurang_2th', 100)->nullable();
            $table->text('pekerjaan_sampingan')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_validasi')->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->longText('ttd_pengawas')->nullable();
            $table->string('nama_pengawas', 150)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nrp');
            $table->index('tanggal_validasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validasi_istirahats');
    }
};
