<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fatigue_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('doc_rank')->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('nomor_unit', 50)->nullable();
            $table->string('area', 100)->nullable();
            $table->foreignId('pengawas_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('tanggal')->nullable();
            $table->time('jam')->nullable();
            $table->string('q1_shift_day', 255)->nullable();
            $table->string('q2_body_condition', 255)->nullable();
            $table->text('q3_reason')->nullable();
            $table->text('q3_background')->nullable();
            $table->string('q4_rest_hours', 50)->nullable();
            $table->string('q5_rest_quality', 255)->nullable();
            $table->text('q5_bad_quality_reason')->nullable();
            $table->string('q6_home_comfort', 255)->nullable();
            $table->text('q6_discomfort_reason')->nullable();
            $table->string('q7_other_activities', 255)->nullable();
            $table->text('q7_activity_details')->nullable();
            $table->text('q8_education')->nullable();
            $table->string('q9_heavy_fatigue_day', 100)->nullable();
            $table->text('q10_fit_plan')->nullable();
            $table->text('q11_suggestions')->nullable();
            $table->longText('ttd_pengawas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fatigue_interviews');
    }
};
