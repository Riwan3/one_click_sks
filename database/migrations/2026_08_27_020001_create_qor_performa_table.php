<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qor_performa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('quartal');
            $table->string('loader', 100)->nullable();
            $table->decimal('jarak', 10, 3)->nullable();
            $table->string('kode_unit', 100)->nullable();
            $table->decimal('rit_total', 10, 2)->default(0);
            $table->decimal('bcm_total', 12, 2)->default(0);
            $table->unsignedInteger('ranking')->nullable();
            $table->unsignedInteger('dari_operator')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'tahun', 'quartal']);
            $table->index(['tahun', 'quartal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qor_performa');
    }
};
