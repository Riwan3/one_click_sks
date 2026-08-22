<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 20)->unique()->after('id');
            $table->string('jabatan')->nullable()->after('name');
            $table->string('unit')->nullable()->after('jabatan');
            $table->string('lokasi')->nullable()->after('unit');
            $table->string('role', 20)->default('operator')->after('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'jabatan', 'unit', 'lokasi', 'role']);
        });
    }
};
