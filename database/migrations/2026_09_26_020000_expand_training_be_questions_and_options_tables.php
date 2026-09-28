<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE training_be_questions MODIFY COLUMN tipe_soal ENUM('multiple_choice','short_text','essay') NOT NULL");

        Schema::table('training_be_questions', function (Blueprint $table) {
            $table->unsignedInteger('duration_seconds')->nullable()->after('urutan');
            $table->boolean('is_required')->default(true)->after('duration_seconds');
            $table->text('correct_answer')->nullable()->after('is_required');
            $table->foreignId('created_by')->nullable()->after('correct_answer')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_at');
            $table->index(['campaign_id', 'urutan']);
        });

        Schema::table('training_be_options', function (Blueprint $table) {
            $table->unsignedInteger('option_order')->default(0)->after('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_be_options', function (Blueprint $table) {
            $table->dropColumn('option_order');
        });

        Schema::table('training_be_questions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'duration_seconds', 'is_required', 'correct_answer',
                'created_by', 'updated_by', 'deleted_at',
            ]);
        });

        DB::statement("ALTER TABLE training_be_questions MODIFY COLUMN tipe_soal ENUM('multiple_choice','essay') NOT NULL");
    }
};
