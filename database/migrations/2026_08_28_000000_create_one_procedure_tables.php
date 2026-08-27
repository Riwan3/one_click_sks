<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Procedures Table
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('Operasional');
            $table->string('revision')->default('Rev. 0');
            $table->text('description')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['active', 'revision_needed', 'expired'])->default('active');
            $table->string('file_path')->nullable();
            $table->string('file_type')->default('pdf');
            $table->date('effective_date')->nullable();
            $table->date('expired_date')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Procedure Assessments Table
        Schema::create('procedure_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->onDelete('cascade');
            $table->string('title');
            $table->integer('passing_grade')->default(80);
            $table->integer('timer_minutes')->default(30);
            $table->integer('retry_limit')->default(3);
            $table->integer('validity_months')->default(6);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('target_roles')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // 3. Procedure Questions Table
        Schema::create('procedure_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('procedure_assessments')->onDelete('cascade');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'case_based'])->default('multiple_choice');
            $table->text('question');
            $table->string('option_a')->nullable();
            $table->string('option_b')->nullable();
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();
            $table->string('correct', 10)->default('A');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        // 4. Procedure Evaluations Table
        Schema::create('procedure_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('procedure_assessments')->onDelete('cascade');
            $table->string('nrp');
            $table->integer('score')->default(0);
            $table->enum('status', ['passed', 'failed', 'remedial_pending', 'coaching_assigned'])->default('passed');
            $table->dateTime('completed_at')->nullable();
            $table->string('certificate_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_evaluations');
        Schema::dropIfExists('procedure_questions');
        Schema::dropIfExists('procedure_assessments');
        Schema::dropIfExists('procedures');
    }
};
