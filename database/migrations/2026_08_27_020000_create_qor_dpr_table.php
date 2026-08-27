<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qor_dpr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('role_op', 20)->default('EXCA');
            $table->date('tanggal');
            $table->string('loader', 100)->nullable();
            $table->decimal('jarak', 10, 3)->nullable();
            $table->string('kode_unit', 100)->nullable();
            $table->decimal('rit', 10, 2)->nullable();
            $table->decimal('total_bcm', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qor_dpr');
    }
};
