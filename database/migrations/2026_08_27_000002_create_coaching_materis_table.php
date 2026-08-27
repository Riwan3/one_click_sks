<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_materis', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->string('kategori', 100); // pribadi, keluarga, lingkungan, pekerjaan
            $table->text('ringkasan');
            $table->longText('deskripsi')->nullable();
            $table->string('file_pdf')->nullable();
            $table->string('video')->nullable();
            $table->string('link_sop')->nullable();
            $table->string('thumbnail')->nullable();
            $table->unsignedSmallInteger('versi')->default(1);
            $table->timestamps();

            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_materis');
    }
};
