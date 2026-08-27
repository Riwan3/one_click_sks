<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_karyawans', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_izin', ['izin', 'sakit', 'emergency']);
            $table->string('no_surat', 100)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('nrp', 20)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();

            // Field jenis "izin" & "emergency"
            $table->text('alasan')->nullable();
            $table->string('tujuan', 150)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->date('tgl_berangkat')->nullable();
            $table->date('tgl_kembali')->nullable();

            // Field jenis "sakit"
            $table->string('no_telepon', 100)->nullable();
            $table->text('keluhan')->nullable();
            $table->string('hasil_td', 20)->nullable();
            $table->string('hasil_rr', 20)->nullable();
            $table->string('hasil_t', 20)->nullable();
            $table->string('hasil_p', 20)->nullable();
            $table->string('hasil_spo', 20)->nullable();
            $table->text('diagnosa')->nullable();
            $table->text('terapi')->nullable();
            $table->unsignedTinyInteger('keterangan_status')->default(1);
            $table->string('rujuk_ke', 150)->nullable();
            $table->string('rumah_sakit_rujukan', 150)->nullable();
            $table->string('dokter_rujukan', 150)->nullable();
            $table->boolean('dipulangkan_tidur')->default(false);

            $table->string('file_bukti')->nullable();
            $table->string('status', 30)->default('Awaiting Approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('jenis_izin');
            $table->index('nrp');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_karyawans');
    }
};
