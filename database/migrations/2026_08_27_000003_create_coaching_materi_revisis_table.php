<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_materi_revisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->constrained('coaching_materis')->cascadeOnDelete();
            $table->unsignedSmallInteger('versi');
            $table->string('judul', 255);
            $table->text('ringkasan');
            $table->longText('deskripsi')->nullable();
            $table->string('file_pdf')->nullable();
            $table->string('video')->nullable();
            $table->string('link_sop')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('revisi_note')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->index('materi_id');
            $table->unique(['materi_id', 'versi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_materi_revisis');
    }
};
