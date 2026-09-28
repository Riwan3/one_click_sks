<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'visibility_mode')) {
                $table->string('visibility_mode', 20)->default('continuous')->after('views_count');
            }
        });

        if (!Schema::hasTable('announcement_reads')) {
            Schema::create('announcement_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('seen_at')->useCurrent();
                $table->unique(['announcement_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'visibility_mode')) {
                $table->dropColumn('visibility_mode');
            }
        });
    }
};
