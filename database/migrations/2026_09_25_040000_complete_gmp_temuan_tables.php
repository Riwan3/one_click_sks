<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lengkapi kolom temuan sesuai gmp_temuan v1 (penugasan PIC, corrective action, verifikasi)
        Schema::table('gmp_temuan', function (Blueprint $table) {
            $table->text('recommendation')->nullable()->after('finding_description');
            $table->string('priority', 50)->nullable()->after('recommendation');
            $table->string('pic_user_id', 100)->nullable()->after('priority')->index();
            $table->string('observer')->nullable()->after('pic_user_id');
            $table->unsignedTinyInteger('progress_percentage')->default(0)->after('due_date');
            $table->text('root_cause')->nullable()->after('progress_percentage');
            $table->text('action_plan')->nullable()->after('root_cause');
            $table->text('actual_action')->nullable()->after('action_plan');
            $table->text('obstacle')->nullable()->after('actual_action');
            $table->date('started_at')->nullable()->after('obstacle');
            $table->date('completed_at')->nullable()->after('started_at');
            $table->dateTime('submitted_for_verification_at')->nullable()->after('completed_at');
            $table->string('verified_by', 100)->nullable()->after('submitted_for_verification_at');
            $table->dateTime('verified_at')->nullable()->after('verified_by');
            $table->string('verification_result', 50)->nullable()->after('verified_at');
            $table->text('verification_note')->nullable()->after('verification_result');
            $table->string('updated_by', 100)->nullable()->after('created_by');
            $table->index('status');
        });

        Schema::table('gmp_temuan_evidence', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('file_path');
            $table->text('description')->nullable()->after('original_name');
        });

        // SLA hari penyelesaian per prioritas (default v1)
        Schema::create('gmp_master_sla', function (Blueprint $table) {
            $table->string('prioritas', 50)->primary();
            $table->unsignedSmallInteger('sla_hari');
        });
        DB::table('gmp_master_sla')->insert([
            ['prioritas' => 'Critical', 'sla_hari' => 1],
            ['prioritas' => 'High', 'sla_hari' => 3],
            ['prioritas' => 'Medium', 'sla_hari' => 7],
            ['prioritas' => 'Low', 'sla_hari' => 14],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gmp_master_sla');

        Schema::table('gmp_temuan_evidence', fn (Blueprint $table) => $table->dropColumn(['original_name', 'description']));

        Schema::table('gmp_temuan', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['pic_user_id']);
            $table->dropColumn([
                'recommendation', 'priority', 'pic_user_id', 'observer', 'progress_percentage', 'root_cause', 'action_plan',
                'actual_action', 'obstacle', 'started_at', 'completed_at', 'submitted_for_verification_at', 'verified_by',
                'verified_at', 'verification_result', 'verification_note', 'updated_by',
            ]);
        });
    }
};
