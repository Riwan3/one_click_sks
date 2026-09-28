<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('deviasi_rekap_pemulangan')) {
            Schema::create('deviasi_rekap_pemulangan', function (Blueprint $table) {
                $table->id();
                $table->string('nrp', 50);
                $table->string('nama', 150);
                $table->string('jabatan', 100)->nullable();
                $table->date('tanggal');
                $table->enum('shift', ['D', 'N'])->default('D');
                $table->time('jam');
                $table->text('alasan')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('konfirmasi', 150)->nullable();
                $table->string('created_by', 100)->nullable();
                $table->string('updated_by', 100)->nullable();
                $table->timestamps();

                $table->index('nrp');
                $table->index('tanggal');
                $table->index('shift');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deviasi_rekap_pemulangan');
    }
};
