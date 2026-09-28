<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_evaluations', function (Blueprint $table) {
            $table->unsignedInteger('attempt')->default(1)->after('nrp');
            $table->dateTime('started_at')->nullable()->after('status');
            $table->unsignedInteger('time_taken')->nullable()->after('completed_at');
        });

        // Tambah state 'in_progress' untuk attempt yang sedang dikerjakan (setara procedure_results di v1)
        DB::statement("ALTER TABLE procedure_evaluations MODIFY COLUMN status ENUM('in_progress','passed','failed','remedial_pending','coaching_assigned') NOT NULL DEFAULT 'passed'");

        // Jawaban per soal untuk autosave & review hasil (setara procedure_answers di v1)
        Schema::create('procedure_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('procedure_evaluations')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('procedure_questions')->cascadeOnDelete();
            $table->string('user_answer', 10)->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
            $table->unique(['evaluation_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_answers');

        DB::statement("ALTER TABLE procedure_evaluations MODIFY COLUMN status ENUM('passed','failed','remedial_pending','coaching_assigned') NOT NULL DEFAULT 'passed'");

        Schema::table('procedure_evaluations', function (Blueprint $table) {
            $table->dropColumn(['attempt', 'started_at', 'time_taken']);
        });
    }
};
