<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DPR bisa memuat NRP yang belum ada di tabel employees (V1 tetap menyimpan dan
 * memakainya, mesin MOR bekerja per NRP). Sebelumnya impor gagal untuk baris itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qor_dpr', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreignId('employee_id')->nullable()->change();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('qor_dpr', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }
};
