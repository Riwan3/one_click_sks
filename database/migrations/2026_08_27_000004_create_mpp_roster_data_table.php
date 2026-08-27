<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_roster_data', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('nrp')->index(); // employee ID
            $table->string('nama');
            $table->string('jabatan')->index(); // job title / position
            $table->string('departemen')->index();
            $table->string('unit')->index();
            $table->enum('shift', ['D', 'N'])->default('D');
            $table->integer('plan_mp')->default(100); // from master categories
            $table->timestamps();

            $table->index(['tanggal', 'nrp']);
            $table->unique(['tanggal', 'nrp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_roster_data');
    }
};
