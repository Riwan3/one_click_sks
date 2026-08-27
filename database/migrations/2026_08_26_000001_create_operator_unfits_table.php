<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_unfits', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20);
            $table->string('nama', 150);
            $table->string('perusahaan', 150)->default('PT. PUTRA PERKASA ABADI');
            $table->string('departemen', 100)->nullable();
            $table->string('contact', 100)->nullable();
            $table->string('diagnosa', 255)->nullable();
            $table->string('dokter_rujukan', 150)->nullable();
            $table->date('tgl_mulai_unfit');
            $table->date('tgl_akhir_unfit')->nullable();
            $table->text('laporan')->nullable();
            $table->string('bukti')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nrp');
            $table->index('tgl_mulai_unfit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_unfits');
    }
};
