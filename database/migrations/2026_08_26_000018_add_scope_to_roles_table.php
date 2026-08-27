<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pisahkan Control Center Role (admin, management — akses One-Core lintas
     * fungsi) dari Site Role (operator, medic, trafficman, dst — role
     * operasional di site ini). Setiap OneClick site adalah instance/database
     * terpisah (domain & VPS sendiri per site), jadi tidak perlu tabel `sites`
     * di sini — site role cukup dikelola sebagai baris `roles` biasa dengan
     * scope = 'site'. Multi-site membership (1 user, beda role di site lain)
     * adalah tanggung jawab Control Center, bukan database site ini.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('scope', 20)->default('site')->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
};
