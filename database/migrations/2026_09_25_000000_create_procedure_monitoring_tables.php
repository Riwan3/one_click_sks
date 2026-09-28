<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Karyawan yang ditugaskan mengerjakan assessment (setara eval_notifications di v1)
        Schema::create('procedure_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('procedure_assessments')->cascadeOnDelete();
            $table->string('nrp')->index();
            $table->timestamps();
            $table->unique(['assessment_id', 'nrp']);
        });

        // Tindak lanjut karyawan tidak lulus: remedial langsung / coaching & mentoring (setara eval_followups di v1)
        Schema::create('procedure_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('procedure_assessments')->cascadeOnDelete();
            $table->string('nrp');
            $table->foreignId('evaluation_id')->nullable()->constrained('procedure_evaluations')->nullOnDelete();
            $table->enum('type', ['remedial_only', 'coaching'])->default('coaching');
            $table->foreignId('mentor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('weak_material_notes')->nullable();
            $table->date('coaching_target_date')->nullable();
            $table->dateTime('remedial_granted_at')->nullable();
            $table->string('remedial_granted_by')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index(['assessment_id', 'nrp']);
        });

        // Backfill: setiap karyawan yang sudah punya hasil evaluasi dianggap sudah ditugaskan
        DB::table('procedure_assignments')->insertUsing(
            ['assessment_id', 'nrp', 'created_at', 'updated_at'],
            DB::table('procedure_evaluations')->selectRaw('DISTINCT assessment_id, nrp, ?, ?', [now(), now()])
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_followups');
        Schema::dropIfExists('procedure_assignments');
    }
};
