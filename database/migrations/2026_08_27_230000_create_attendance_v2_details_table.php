<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_v2_details', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20);
            $table->string('name', 150)->nullable();
            $table->string('position', 100)->nullable();
            $table->date('tanggal');
            $table->string('jam_in', 10)->nullable();
            $table->string('jam_out', 10)->nullable();
            $table->string('job', 150)->nullable();
            $table->string('dept', 100)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('sia_category', 5)->nullable();
            $table->string('device', 100)->nullable();
            $table->string('owner', 100)->nullable();
            $table->integer('device_count')->default(0);
            $table->string('device_id', 100)->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'nrp']);
            $table->index('sia_category');
            $table->index('code');
            $table->index('dept');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_v2_details');
    }
};
