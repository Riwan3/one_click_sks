<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemanggilan_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nrp', 20)->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->text('alasan_pemanggilan')->nullable();
            $table->string('tempat', 150)->nullable();
            $table->time('jam_pemanggilan')->nullable();
            $table->date('tanggal_pemanggilan')->nullable();
            $table->string('nama_manpower', 150)->nullable();
            $table->string('fatigue_periode', 20)->nullable();
            $table->longText('ttd_pemanggil')->nullable();
            $table->longText('ttd_karyawan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nrp');
            $table->index('tanggal_pemanggilan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemanggilan_karyawans');
    }
};
