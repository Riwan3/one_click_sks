<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sk_pembinaans', function (Blueprint $table) {
            if (! Schema::hasColumn('sk_pembinaans', 'foto')) {
                $table->string('foto', 500)->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sk_pembinaans', function (Blueprint $table) {
            if (Schema::hasColumn('sk_pembinaans', 'foto')) {
                $table->dropColumn('foto');
            }
        });
    }
};
