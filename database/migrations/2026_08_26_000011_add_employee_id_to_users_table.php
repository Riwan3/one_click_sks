<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * employees = master data kepegawaian (nrp, jabatan, status, dst).
     * users = akun login/authentication. Relasi 0..1 — tidak semua employee
     * punya akun, satu employee cukup satu akun (tapi bisa aktif di banyak
     * site lewat user_site_roles).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('id')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
    }
};
