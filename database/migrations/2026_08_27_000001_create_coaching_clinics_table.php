<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->string('lokasi', 255);
            $table->time('waktu_pertemuan');
            $table->text('materi_pribadi')->nullable();
            $table->text('materi_keluarga')->nullable();
            $table->text('materi_lingkungan')->nullable();
            $table->text('materi_pekerjaan')->nullable();
            $table->unsignedTinyInteger('fatigue_level')->default(0); // 0-3
            $table->longText('deskripsi')->nullable();
            $table->longText('rekomendasi')->nullable();
            $table->string('file_bukti')->nullable();
            $table->longText('ttd_anak_asuh')->nullable(); // base64 canvas signature
            $table->longText('ttd_bapak_asuh')->nullable(); // base64 canvas signature
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('coach_id');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_clinics');
    }
};
