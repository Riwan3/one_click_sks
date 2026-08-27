<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('posisi', 150)->nullable();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->decimal('atr_persen', 6, 2)->default(0);
            $table->decimal('atr_point', 6, 2)->default(0);
            $table->unsignedInteger('work_day')->default(0);
            $table->unsignedInteger('sakit')->default(0);
            $table->unsignedInteger('ijin')->default(0);
            $table->unsignedInteger('alpa')->default(0);
            $table->decimal('timesheet_persen', 6, 2)->default(0);
            $table->decimal('timesheet_point', 6, 2)->default(0);
            $table->decimal('hm_ratio', 6, 2)->default(0);
            $table->decimal('hm_point', 6, 2)->default(0);
            $table->decimal('hm_jam', 10, 2)->default(0);
            $table->decimal('hm_target', 10, 2)->default(0);
            $table->decimal('fuel_persen', 6, 2)->default(0);
            $table->decimal('fuel_point', 6, 2)->default(0);
            $table->decimal('fuel_actual', 10, 2)->default(0);
            $table->decimal('fuel_limit', 10, 2)->default(0);
            $table->decimal('pdty_persen', 6, 2)->nullable();
            $table->decimal('pdty_point', 6, 2)->nullable();
            $table->decimal('act_coal', 12, 2)->nullable();
            $table->decimal('targt_coal', 12, 2)->nullable();
            $table->decimal('act_ob', 12, 2)->nullable();
            $table->decimal('targt_ob', 12, 2)->nullable();
            $table->unsignedInteger('pembinaan')->default(0);
            $table->decimal('pembinaan_poin', 6, 2)->default(20);
            $table->decimal('jumlah_point', 6, 2)->default(0);
            $table->decimal('total_poin', 6, 2)->default(0);
            $table->unsignedInteger('ranking')->nullable();
            $table->unsignedInteger('dari_operator')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'bulan', 'tahun']);
            $table->index(['bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mor_data');
    }
};
