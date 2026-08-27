<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_counselings', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('sp_id')->nullable()->constrained('surat_peringatans')->nullOnDelete();
            $table->foreignId('sk_id')->nullable()->constrained('sk_pembinaans')->nullOnDelete();
            $table->date('tanggal');
            $table->string('lokasi', 255)->nullable();
            $table->time('waktu_pertemuan')->nullable();
            $table->text('materi_pribadi')->nullable();
            $table->text('materi_keluarga')->nullable();
            $table->text('materi_lingkungan')->nullable();
            $table->text('materi_pekerjaan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->foreignId('coach_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('fatigue_level')->default(0);
            $table->longText('ttd_anak_asuh')->nullable();
            $table->longText('ttd_bapa_asuh')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_counselings');
    }
};
