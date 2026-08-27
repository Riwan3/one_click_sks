<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpp_master_categories', function (Blueprint $table) {
            $table->enum('kategori_tipe', ['staff', 'pengawas'])
                ->default('staff')
                ->after('kode')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('mpp_master_categories', function (Blueprint $table) {
            $table->dropColumn('kategori_tipe');
        });
    }
};
