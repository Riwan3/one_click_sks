<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sk_pembinaans', function (Blueprint $table) {
            $table->longText('ttd_pja')->nullable()->after('ttd_atasan');
            $table->longText('ttd_hse')->nullable()->after('ttd_pja');
        });
    }

    public function down(): void
    {
        Schema::table('sk_pembinaans', function (Blueprint $table) {
            $table->dropColumn(['ttd_pja', 'ttd_hse']);
        });
    }
};
