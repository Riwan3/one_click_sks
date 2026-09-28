<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_be_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('training_be_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('training_be_questions')->cascadeOnDelete();
            $table->mediumText('answer_text')->nullable();
            $table->foreignId('selected_option_id')->nullable()->constrained('training_be_options')->nullOnDelete();
            $table->boolean('is_answered')->default(false);
            $table->boolean('is_timed_out')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->dateTime('answered_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->decimal('automatic_score', 8, 2)->nullable();
            $table->decimal('manual_score', 8, 2)->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('reviewer_note', 500)->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });

        Schema::create('training_be_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('training_be_campaigns')->cascadeOnDelete();
            $table->foreignId('attempt_id')->nullable()->constrained('training_be_attempts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 50);
            $table->string('description', 500)->default('');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['campaign_id', 'action_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_be_activity_logs');
        Schema::dropIfExists('training_be_answers');
    }
};
