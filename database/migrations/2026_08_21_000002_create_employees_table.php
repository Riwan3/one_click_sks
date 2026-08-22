<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20)->unique();
            $table->string('name', 150);
            $table->string('jabatan_ss6', 100)->nullable();
            $table->string('jabatan_roster', 100)->nullable();
            $table->string('status_karyawan', 20)->default('FIT');
            $table->string('lokasi_kerja', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('no_telepon', 100)->nullable();
            $table->date('efektif_bekerja')->nullable();
            $table->text('input_here')->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();

            $table->index('status_karyawan');
            $table->index('jabatan_ss6');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
