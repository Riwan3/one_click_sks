<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmp_master_lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_gmp', 50); // MHR, MFOB, MFCO, DISPOSAL, DEWATERING
            $table->string('nama_lokasi', 255);
            $table->timestamps();

            $table->index('tipe_gmp');
        });

        Schema::create('gmp_penilaian_form', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_gmp', 50);
            $table->string('week', 50)->nullable();
            $table->string('lokasi', 255);
            $table->string('penilai', 255);
            $table->string('nrp', 100);
            $table->date('tanggal_gmp');
            $table->string('foto_area', 500)->nullable();
            $table->integer('total_score')->default(0);
            $table->float('total_percent')->default(0);
            $table->integer('question_count')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('created_by', 150)->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, returned
            $table->string('approved_by', 150)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('revision_note')->nullable();
            $table->timestamps();

            $table->index(['tipe_gmp', 'status']);
            $table->index('tanggal_gmp');
        });

        Schema::create('gmp_penilaian_form_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('gmp_penilaian_form')->cascadeOnDelete();
            $table->integer('question_no');
            $table->string('section_key', 50)->default('teknis');
            $table->string('criterion_title', 255);
            $table->string('criterion_name', 255);
            $table->integer('score_raw')->default(0);
            $table->float('score_percent')->default(0);
            $table->string('foto_detail', 500)->nullable();
            $table->timestamps();

            $table->index('form_id');
        });

        Schema::create('gmp_temuan', function (Blueprint $table) {
            $table->id();
            $table->string('finding_code', 100)->unique();
            $table->string('source_type', 100)->default('Penilaian GMP');
            $table->unsignedBigInteger('assessment_id')->nullable();
            $table->unsignedBigInteger('assessment_detail_id')->nullable();
            $table->integer('year')->nullable();
            $table->string('week', 50)->nullable();
            $table->string('area_id', 50)->nullable();
            $table->string('location_id', 255)->nullable();
            $table->string('aspect_id', 100)->nullable();
            $table->string('checklist_id', 255)->nullable();
            $table->text('standard_description')->nullable();
            $table->text('finding_description')->nullable();
            $table->date('finding_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('created_by', 150)->nullable();
            $table->string('status', 50)->default('draft');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('gmp_temuan_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('gmp_temuan')->cascadeOnDelete();
            $table->string('evidence_type', 20)->default('before'); // before, after
            $table->string('file_path', 500);
            $table->string('uploaded_by', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('gmp_temuan_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finding_id')->constrained('gmp_temuan')->cascadeOnDelete();
            $table->string('action_type', 50);
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('created_by', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gmp_temuan_history');
        Schema::dropIfExists('gmp_temuan_evidence');
        Schema::dropIfExists('gmp_temuan');
        Schema::dropIfExists('gmp_penilaian_form_detail');
        Schema::dropIfExists('gmp_penilaian_form');
        Schema::dropIfExists('gmp_master_lokasi');
    }
};
