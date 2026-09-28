<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel masih kosong - realign penuh ke skema v1 (employee_id + status workflow)
        // supaya query monitoring/raport (join notifikasi -> hasil terakhir) konsisten.
        DB::statement('ALTER TABLE academy_results DROP COLUMN nrp');
        DB::statement('ALTER TABLE academy_results DROP COLUMN nama');
        DB::statement('ALTER TABLE academy_results DROP COLUMN jabatan');
        DB::statement('ALTER TABLE academy_results DROP COLUMN is_passed');
        DB::statement('ALTER TABLE academy_results DROP COLUMN total_questions');
        DB::statement('ALTER TABLE academy_results DROP COLUMN correct_answers');
        DB::statement('ALTER TABLE academy_results DROP COLUMN attempt_count');

        Schema::table('academy_results', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('assessment_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt')->default(1)->after('employee_id');
            $table->boolean('passed')->nullable()->after('score');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->after('passed');
            $table->dateTime('started_at')->nullable()->after('status');
            $table->unsignedInteger('time_taken')->nullable()->comment('detik')->after('completed_at');
            $table->index(['assessment_id', 'employee_id']);
        });

        Schema::create('academy_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('academy_assessments')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['assessment_id', 'employee_id']);
        });

        Schema::create('academy_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('academy_results')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('academy_questions')->cascadeOnDelete();
            $table->char('user_answer', 1)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['result_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_answers');
        Schema::dropIfExists('academy_notifications');

        Schema::table('academy_results', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'attempt', 'passed', 'status', 'started_at', 'time_taken']);
        });

        Schema::table('academy_results', function (Blueprint $table) {
            $table->string('nrp')->nullable();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->integer('total_questions')->nullable();
            $table->integer('correct_answers')->nullable();
            $table->integer('attempt_count')->nullable();
        });
    }
};
