<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roster_monthly_master')) {
            Schema::create('roster_monthly_master', function (Blueprint $table) {
                $table->id();
                $table->string('nrp', 50)->index();
                $table->date('tanggal')->index();
                $table->string('kode', 20)->nullable();
                $table->string('unit', 50)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->unique(['nrp', 'tanggal'], 'uk_nrp_tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_monthly_master');
    }
};
