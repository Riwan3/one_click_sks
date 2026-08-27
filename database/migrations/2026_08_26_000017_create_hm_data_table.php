<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hm_data', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('shift', 10);
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('unit', 50);
            $table->string('dept', 100)->nullable();
            $table->decimal('hm_start', 10, 2);
            $table->decimal('hm_stop', 10, 2);
            $table->decimal('hm_diff', 10, 2);
            $table->string('activity', 150)->nullable();
            $table->string('parking', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tanggal', 'shift', 'employee_id', 'unit']);
            $table->index('tanggal');
            $table->index('unit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hm_data');
    }
};
