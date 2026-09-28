<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'surat_peringatans',
        'pakta_integritas',
        'surat_tegurans',
        'pemanggilan_karyawans',
        'validasi_istirahats',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'foto')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('foto', 500)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'foto')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('foto');
                });
            }
        }
    }
};
