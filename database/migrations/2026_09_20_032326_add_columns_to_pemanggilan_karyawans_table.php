<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemanggilan_karyawans', function (Blueprint $table) {
            $table->string('no_surat', 100)->nullable()->after('id')->index();
            $table->string('ttd_pemanggilan', 150)->nullable()->after('jam_pemanggilan');
            $table->string('eviden_1', 255)->nullable()->after('ttd_karyawan');
            $table->string('eviden_2', 255)->nullable()->after('eviden_1');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemanggilan_karyawans', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['no_surat', 'ttd_pemanggilan', 'eviden_1', 'eviden_2', 'updated_by']);
        });
    }
};
