<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odr_master_area', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150)->unique();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('odr_master_unit', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->nullable();
            $table->string('nama', 150);
            $table->string('area', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('odr_master_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 200);
            $table->string('kategori', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('odr_assignments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('shift', 20)->default('Shift 1');
            $table->string('area', 100)->index();
            $table->string('unit', 100);
            $table->string('pengawas', 150)->index();
            $table->text('aktivitas')->nullable(); // JSON list of tasks
            $table->text('task_done')->nullable(); // JSON boolean array of done statuses
            $table->text('keterangan')->nullable();
            $table->enum('status', ['selesai', 'progress', 'belum'])->default('belum')->index();
            $table->boolean('gmp')->default(false);
            $table->boolean('inspeksi')->default(false);
            $table->boolean('high_risk')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odr_assignments');
        Schema::dropIfExists('odr_master_aktivitas');
        Schema::dropIfExists('odr_master_unit');
        Schema::dropIfExists('odr_master_area');
    }
};
