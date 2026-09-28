<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->boolean('randomize_questions')->default(false)->after('validity_months');
            $table->boolean('mandatory_read')->default(false)->after('randomize_questions');
            $table->foreignId('created_by')->nullable()->after('mandatory_read')->constrained('users')->nullOnDelete();
        });

        // signed_by di v1 = FK ke employees (Section/Dept Head boleh tidak punya akun login),
        // bukan users - tabel masih kosong jadi aman diubah tipe datanya.
        DB::statement('ALTER TABLE academy_assessments DROP COLUMN signed_by');
        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->foreignId('signed_by')->nullable()->after('mandatory_read')->constrained('employees')->nullOnDelete();
        });

        DB::statement('ALTER TABLE academy_materi DROP COLUMN created_by');
        Schema::table('academy_materi', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('revision')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('academy_materi', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
        Schema::table('academy_materi', function (Blueprint $table) {
            $table->string('created_by')->nullable();
        });

        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['signed_by', 'created_by', 'mandatory_read', 'randomize_questions']);
        });
        Schema::table('academy_assessments', function (Blueprint $table) {
            $table->string('signed_by')->nullable();
        });
    }
};
