<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Monitoring Grade & Jabatan
        if (!Schema::hasTable('odp_mg_grade_v4')) {
            Schema::create('odp_mg_grade_v4', function (Blueprint $table) {
                $table->id();
                $table->string('nrp')->nullable();
                $table->string('nama')->nullable();
                $table->string('posisi')->nullable();
                $table->string('eg')->nullable();
                $table->string('sub_area')->nullable();
                $table->string('atr_6_bulan')->nullable();
                $table->string('join_date')->nullable();
                $table->string('efektif_grade')->nullable();
                $table->string('efektif_position')->nullable();
                $table->string('grade_now')->nullable();
                $table->string('min_level')->nullable();
                $table->string('max_level')->nullable();
                $table->string('status_level')->nullable();
                $table->string('suite')->nullable();
                $table->string('level_posisi')->nullable();
                $table->string('jumlah_versatility')->nullable();
                $table->string('requirement_versatility')->nullable();
                $table->string('gap_versatility')->nullable();
                $table->string('kesesuaian_versatility')->nullable();
                $table->string('masa_kerja')->nullable();
                $table->string('masa_kerja_grade_sekarang')->nullable();
                $table->string('masa_kerja_posisi_sekarang')->nullable();
                $table->string('masa_grade_3_th_up')->nullable();
                $table->string('masa_position_3th_up')->nullable();
                $table->text('sertifikasi')->nullable();
                $table->string('status_sertifikasi')->nullable();
                $table->string('expired_date_sertifikasi')->nullable();
                $table->string('remaining_time')->nullable();
                $table->text('remarks_grade')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_mg_versality_v5')) {
            Schema::create('odp_mg_versality_v5', function (Blueprint $table) {
                $table->id();
                $table->string('nrp')->nullable();
                $table->string('nama')->nullable();
                $table->string('jabatan')->nullable();
                $table->text('versality')->nullable();
                
                $table->text('grp1_wt_ft_lt')->nullable();
                $table->text('grp1_forklift')->nullable();
                $table->text('grp1_slurry_pump')->nullable();
                
                $table->text('grp2_pc200')->nullable();
                $table->text('grp2_crane_3t')->nullable();
                $table->text('grp2_tyre_handler')->nullable();
                $table->text('grp2_bw211d')->nullable();
                $table->text('grp2_wa200')->nullable();
                $table->text('grp2_sv700')->nullable();
                
                $table->text('grp3_pc200_amphibi')->nullable();
                $table->text('grp3_pc500')->nullable();
                $table->text('grp3_d85')->nullable();
                $table->text('grp3_gd511')->nullable();
                $table->text('grp3_dt_20_30t')->nullable();
                
                $table->text('grp4_crane_10_20t')->nullable();
                $table->text('grp4_telecopic')->nullable();
                $table->text('grp4_wa400')->nullable();
                $table->text('grp4_pc750')->nullable();
                $table->text('grp4_adt_hm400')->nullable();
                $table->text('grp4_d155')->nullable();
                $table->text('grp4_wa500')->nullable();
                $table->text('grp4_gd705')->nullable();
                $table->text('grp4_dt_35_50t')->nullable();
                $table->text('grp4_lowboy')->nullable();
                $table->text('grp4_dm245')->nullable();
                
                $table->text('grp5_pc1250')->nullable();
                $table->text('grp5_hd465')->nullable();
                $table->text('grp5_adt_volvo')->nullable();
                $table->text('grp5_crane_50_70t')->nullable();
                $table->text('grp5_d375')->nullable();
                $table->text('grp5_gd825')->nullable();
                $table->text('grp5_wa600')->nullable();
                $table->text('grp5_single_trailer')->nullable();
                
                $table->text('grp6_pc2000')->nullable();
                $table->text('grp6_hd785')->nullable();
                $table->text('grp6_wa800')->nullable();
                $table->text('grp6_double_trailer')->nullable();
                
                $table->text('grp7_pc3400')->nullable();
                $table->text('grp7_hd1500')->nullable();
                $table->text('grp7_d475')->nullable();
                
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('odp_mg_settings')) {
            Schema::create('odp_mg_settings', function (Blueprint $table) {
                $table->string('setting_key', 50)->primary();
                $table->text('setting_value')->nullable();
                $table->timestamps();
            });
        }

        // 2. Evaluasi Kompetensi
        if (!Schema::hasTable('academy_materi')) {
            Schema::create('academy_materi', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category')->nullable();
                $table->string('revision')->default('Rev. 0');
                $table->text('description')->nullable();
                $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->enum('status', ['active', 'revision_needed', 'expired'])->default('active');
                $table->date('effective_date')->nullable();
                $table->date('expired_date')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_type')->default('pdf');
                $table->string('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('academy_assessments')) {
            Schema::create('academy_assessments', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->foreignId('procedure_id')->nullable()->constrained('academy_materi')->onDelete('cascade');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->integer('passing_grade')->default(70);
                $table->integer('timer_minutes')->default(30);
                $table->integer('retry_limit')->default(3);
                $table->integer('validity_months')->default(6);
                $table->string('signed_by')->nullable();
                $table->text('signer_signature')->nullable();
                $table->timestamp('signer_signed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('academy_questions')) {
            Schema::create('academy_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('academy_assessments')->onDelete('cascade');
                $table->enum('question_type', ['multiple_choice', 'true_false', 'case_based'])->default('multiple_choice');
                $table->text('question');
                $table->text('option_a')->nullable();
                $table->text('option_b')->nullable();
                $table->text('option_c')->nullable();
                $table->text('option_d')->nullable();
                $table->string('correct', 10)->nullable();
                $table->text('explanation')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('academy_targets')) {
            Schema::create('academy_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('academy_assessments')->onDelete('cascade');
                $table->string('jabatan');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('academy_results')) {
            Schema::create('academy_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->constrained('academy_assessments')->onDelete('cascade');
                $table->string('nrp');
                $table->string('nama')->nullable();
                $table->string('jabatan')->nullable();
                $table->float('score')->default(0);
                $table->boolean('is_passed')->default(false);
                $table->integer('total_questions')->default(0);
                $table->integer('correct_answers')->default(0);
                $table->integer('attempt_count')->default(1);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Best Employee
        if (!Schema::hasTable('training_be_campaigns')) {
            Schema::create('training_be_campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('judul');
                $table->text('deskripsi')->nullable();
                $table->text('aturan')->nullable();
                $table->dateTime('publish_start')->nullable();
                $table->dateTime('publish_end')->nullable();
                $table->enum('status', ['draft', 'scheduled', 'active', 'finished', 'cancelled'])->default('draft');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('training_be_questions')) {
            Schema::create('training_be_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('training_be_campaigns')->onDelete('cascade');
                $table->enum('tipe_soal', ['multiple_choice', 'essay'])->default('multiple_choice');
                $table->text('pertanyaan');
                $table->float('bobot')->default(1.0);
                $table->integer('urutan')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('training_be_options')) {
            Schema::create('training_be_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('training_be_questions')->onDelete('cascade');
                $table->text('option_text');
                $table->boolean('is_correct')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('training_be_participants')) {
            Schema::create('training_be_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('training_be_campaigns')->onDelete('cascade');
                $table->string('user_nrp');
                $table->string('user_nama')->nullable();
                $table->string('user_jabatan')->nullable();
                $table->string('user_unit')->nullable();
                $table->enum('status', ['eligible', 'blocked', 'completed'])->default('eligible');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('training_be_attempts')) {
            Schema::create('training_be_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('training_be_campaigns')->onDelete('cascade');
                $table->foreignId('participant_id')->constrained('training_be_participants')->onDelete('cascade');
                $table->string('user_nrp');
                $table->dateTime('started_at')->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->float('total_score')->default(0);
                $table->float('objective_score')->default(0);
                $table->float('manual_score')->default(0);
                $table->enum('status', ['belum_mulai', 'in_progress', 'submitted', 'expired', 'timed_out', 'reset'])->default('belum_mulai');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_be_attempts');
        Schema::dropIfExists('training_be_participants');
        Schema::dropIfExists('training_be_options');
        Schema::dropIfExists('training_be_questions');
        Schema::dropIfExists('training_be_campaigns');

        Schema::dropIfExists('academy_results');
        Schema::dropIfExists('academy_targets');
        Schema::dropIfExists('academy_questions');
        Schema::dropIfExists('academy_assessments');
        Schema::dropIfExists('academy_materi');

        Schema::dropIfExists('odp_mg_settings');
        Schema::dropIfExists('odp_mg_versality_v5');
        Schema::dropIfExists('odp_mg_grade_v4');
    }
};
