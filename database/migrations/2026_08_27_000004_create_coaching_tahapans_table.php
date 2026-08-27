<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaching_tahapans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama', 255);
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->unsignedSmallInteger('durasi_target')->nullable(); // dalam hari
            $table->boolean('wajib')->default(true);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('aktif');
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaching_tahapans');
    }
};
