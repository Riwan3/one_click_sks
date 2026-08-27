<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coaching_clinics', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('published')->after('created_by');
            $table->softDeletes()->after('status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coaching_clinics', function (Blueprint $table) {
            $table->dropIndex('coaching_clinics_status_index');
            $table->dropColumn(['status', 'deleted_at']);
        });
    }
};
