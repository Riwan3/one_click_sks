<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cluster Setting Fleet harian (nama cluster == fleet_allocations.group_name). Cluster kosong hanya hidup di tabel ini.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_clusters', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedTinyInteger('shift'); // 1 = siang (D), 2 = malam (N)
            $table->string('name', 100);
            $table->string('area', 100)->nullable(); // wilayah umum, mis. Timur / Tengah / Barat
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('created_by', 50)->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'shift', 'name']);
            $table->index(['tanggal', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_clusters');
    }
};
