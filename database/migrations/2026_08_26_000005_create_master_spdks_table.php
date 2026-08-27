<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_spdks', function (Blueprint $table) {
            $table->id();
            $table->string('no_pelanggaran', 20)->nullable();
            $table->string('kategori', 100)->nullable();
            $table->text('jenis_pelanggaran')->nullable();
            $table->string('sanksi', 100)->nullable();
            $table->text('keterangan_sanksi')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_spdks');
    }
};
