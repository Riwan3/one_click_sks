<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_be_campaigns', function (Blueprint $table) {
            $table->dateTime('latest_start_at')->nullable()->after('publish_end');
            $table->string('timezone', 64)->default('Asia/Makassar')->after('latest_start_at');
            $table->unsignedInteger('default_question_duration_seconds')->default(60)->after('timezone');
            $table->unsignedInteger('total_duration_minutes')->nullable()->after('default_question_duration_seconds');
            $table->unsignedTinyInteger('attempt_limit')->default(1)->after('total_duration_minutes');
            $table->boolean('shuffle_questions')->default(false)->after('attempt_limit');
            $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
            $table->boolean('allow_previous_question')->default(false)->after('shuffle_options');
            $table->boolean('auto_submit')->default(true)->after('allow_previous_question');
            $table->boolean('show_score_after_submit')->default(false)->after('auto_submit');
            $table->boolean('show_correct_answer')->default(false)->after('show_score_after_submit');
            $table->foreignId('published_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->dateTime('published_at')->nullable()->after('published_by');
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_at');
            $table->index('status');
            $table->index(['publish_start', 'publish_end']);
        });
    }

    public function down(): void
    {
        Schema::table('training_be_campaigns', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'latest_start_at', 'timezone', 'default_question_duration_seconds',
                'total_duration_minutes', 'attempt_limit', 'shuffle_questions', 'shuffle_options',
                'allow_previous_question', 'auto_submit', 'show_score_after_submit', 'show_correct_answer',
                'published_by', 'published_at', 'created_by', 'updated_by', 'deleted_at',
            ]);
        });
    }
};
