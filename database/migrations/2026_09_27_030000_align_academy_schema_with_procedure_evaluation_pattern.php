<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Semua tabel academy_* masih kosong - migration ini menyelaraskan skema dengan pola
 * yang sudah dipakai modul kembar "Evaluasi Prosedur" (procedure_assessments dkk),
 * yaitu identifikasi peserta via nrp (bukan employee_id) dan target_roles sebagai
 * kolom JSON di assessment (bukan tabel pivot terpisah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->json('target_roles')->nullable()->after('mandatory_read');
        });

        DB::table('academy_targets')->orderBy('assessment_id')->get()->groupBy('assessment_id')
            ->each(function ($rows, $assessmentId) {
                DB::table('academy_assessments')->where('id', $assessmentId)
                    ->update(['target_roles' => json_encode($rows->pluck('jabatan')->values())]);
            });
        Schema::dropIfExists('academy_targets');

        DB::statement('ALTER TABLE academy_assessments DROP FOREIGN KEY academy_assessments_created_by_foreign');
        DB::statement('ALTER TABLE academy_assessments DROP COLUMN created_by');
        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('signer_signed_at');
        });

        DB::statement('ALTER TABLE academy_materi DROP FOREIGN KEY academy_materi_created_by_foreign');
        DB::statement('ALTER TABLE academy_materi DROP COLUMN created_by');
        Schema::table('academy_materi', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('revision');
        });

        // academy_answers/academy_notifications/academy_results semuanya baru dibuat
        // migration sebelumnya & masih kosong - drop+recreate lebih sederhana & aman
        // daripada ALTER kolom FK bertahap (academy_answers ikut di-drop dulu krn FK-nya
        // menunjuk academy_results).
        Schema::dropIfExists('academy_answers');
        Schema::dropIfExists('academy_notifications');
        Schema::dropIfExists('academy_results');

        Schema::create('academy_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('academy_assessments')->cascadeOnDelete();
            $table->string('nrp');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['assessment_id', 'nrp']);
        });

        Schema::create('academy_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('academy_assessments')->cascadeOnDelete();
            $table->string('nrp');
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->double('score')->default(0);
            $table->boolean('passed')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('time_taken')->nullable()->comment('detik');
            $table->timestamps();
            $table->index(['assessment_id', 'nrp']);
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
        Schema::dropIfExists('academy_results');

        Schema::create('academy_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('academy_assessments')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->double('score')->default(0);
            $table->boolean('passed')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('time_taken')->nullable();
            $table->timestamps();
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

        Schema::table('academy_materi', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->foreignId('created_by')->nullable()->after('revision')->constrained('users')->nullOnDelete();
        });

        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->foreignId('created_by')->nullable()->after('signer_signed_at')->constrained('users')->nullOnDelete();
            $table->dropColumn('target_roles');
        });

        Schema::create('academy_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('academy_assessments')->cascadeOnDelete();
            $table->string('jabatan');
            $table->timestamps();
        });
    }
};
