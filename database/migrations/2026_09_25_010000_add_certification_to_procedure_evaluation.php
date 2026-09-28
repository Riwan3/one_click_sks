<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_assessments', function (Blueprint $table) {
            $table->boolean('randomize_questions')->default(false)->after('validity_months');
            $table->boolean('mandatory_read')->default(false)->after('randomize_questions');
            $table->foreignId('signed_by')->nullable()->after('target_roles')->constrained('employees')->nullOnDelete();
            $table->longText('signer_signature')->nullable()->after('signed_by');
            $table->dateTime('signer_signed_at')->nullable()->after('signer_signature');
        });

        // Sertifikat kompetensi (setara procedure_certificates di v1)
        Schema::create('procedure_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('procedure_assessments')->cascadeOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained('procedure_evaluations')->nullOnDelete();
            $table->string('nrp')->index();
            $table->string('certificate_code', 50)->unique();
            $table->decimal('score', 5, 2)->nullable();
            $table->date('issue_date');
            $table->date('valid_until');
            $table->enum('status', ['active', 'expiring_soon', 'expired', 'revoked'])->default('active')->index();
            $table->string('issued_by')->nullable();
            $table->timestamps();
        });

        // Backfill dari kode sertifikat lama yang tersimpan di procedure_evaluations
        DB::table('procedure_evaluations as pe')
            ->join('procedure_assessments as a', 'a.id', '=', 'pe.assessment_id')
            ->whereNotNull('pe.certificate_code')
            ->where('pe.status', 'passed')
            ->select('pe.*', 'a.validity_months')
            ->orderBy('pe.id')
            ->each(function ($row) {
                $issueDate = \Illuminate\Support\Carbon::parse($row->completed_at ?? $row->created_at);
                DB::table('procedure_certificates')->insertOrIgnore([
                    'assessment_id' => $row->assessment_id,
                    'evaluation_id' => $row->id,
                    'nrp' => $row->nrp,
                    'certificate_code' => $row->certificate_code,
                    'score' => $row->score,
                    'issue_date' => $issueDate->toDateString(),
                    'valid_until' => (int) $row->validity_months > 0 ? $issueDate->copy()->addMonths((int) $row->validity_months)->toDateString() : '9999-12-31',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_certificates');

        Schema::table('procedure_assessments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by');
            $table->dropColumn(['randomize_questions', 'mandatory_read', 'signer_signature', 'signer_signed_at']);
        });
    }
};
