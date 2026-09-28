<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coaching_counselings', function (Blueprint $table) {
            if (! Schema::hasColumn('coaching_counselings', 'file_bukti')) {
                $table->string('file_bukti', 255)->nullable()->after('rekomendasi');
            }
            if (! Schema::hasColumn('coaching_counselings', 'file_slip')) {
                $table->string('file_slip', 255)->nullable()->after('file_bukti');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coaching_counselings', function (Blueprint $table) {
            if (Schema::hasColumn('coaching_counselings', 'file_slip')) {
                $table->dropColumn('file_slip');
            }
            if (Schema::hasColumn('coaching_counselings', 'file_bukti')) {
                $table->dropColumn('file_bukti');
            }
        });
    }
};
