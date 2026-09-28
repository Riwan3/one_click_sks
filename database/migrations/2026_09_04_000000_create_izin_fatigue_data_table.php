<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('izin_fatigue_data')) {
            Schema::create('izin_fatigue_data', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 20);
                $table->string('nrp')->nullable();
                $table->string('nama_operator')->nullable();
                $table->string('jam_mulai', 10)->nullable();
                $table->string('jam_akhir', 10)->nullable();
                $table->string('total_jam', 50)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_fatigue_data');
    }
};
