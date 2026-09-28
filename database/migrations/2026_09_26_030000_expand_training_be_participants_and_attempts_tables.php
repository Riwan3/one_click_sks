<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE training_be_participants MODIFY COLUMN status ENUM('assigned','removed') NOT NULL DEFAULT 'assigned'");

        Schema::table('training_be_participants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('campaign_id')->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable()->after('assigned_by');
            $table->foreignId('removed_by')->nullable()->after('assigned_at')->constrained('users')->nullOnDelete();
            $table->dateTime('removed_at')->nullable()->after('removed_by');
            $table->unique(['campaign_id', 'user_id']);
        });

        Schema::table('training_be_attempts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('participant_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('attempt_number')->default(1)->after('user_id');
            $table->string('expired_reason', 255)->nullable()->after('status');
            $table->string('reset_reason', 255)->nullable()->after('expired_reason');
            $table->foreignId('reset_by')->nullable()->after('reset_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('reset_at')->nullable()->after('reset_by');
        });
    }

    public function down(): void
    {
        Schema::table('training_be_attempts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reset_by']);
            $table->dropColumn(['user_id', 'attempt_number', 'expired_reason', 'reset_reason', 'reset_by', 'reset_at']);
        });

        Schema::table('training_be_participants', function (Blueprint $table) {
            $table->dropUnique(['campaign_id', 'user_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropForeign(['removed_by']);
            $table->dropColumn(['user_id', 'assigned_by', 'assigned_at', 'removed_by', 'removed_at']);
        });

        DB::statement("ALTER TABLE training_be_participants MODIFY COLUMN status ENUM('eligible','blocked','completed') NOT NULL DEFAULT 'eligible'");
    }
};
