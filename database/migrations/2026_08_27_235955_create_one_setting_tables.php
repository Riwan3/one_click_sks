<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roster_data')) {
            Schema::create('roster_data', function (Blueprint $table) {
                $table->id();
                $table->string('nrp')->index();
                $table->date('tanggal')->index();
                $table->string('unit')->index();
                $table->string('kode', 10)->default('D')->index();
                $table->string('csa_type')->nullable();
                $table->timestamps();

                $table->unique(['nrp', 'tanggal', 'kode'], 'roster_data_nrp_tanggal_kode_unique');
            });
        }

        if (!Schema::hasTable('fleet_allocations')) {
            Schema::create('fleet_allocations', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal')->index();
                $table->integer('shift')->default(1)->index();
                $table->string('group_name')->nullable()->index();
                $table->string('loader_id')->nullable()->index();
                $table->string('area')->nullable();
                $table->string('front')->nullable();
                $table->string('refueling_time')->nullable();
                $table->string('unit_nrp')->nullable();
                $table->string('unit_status', 10)->nullable();
                $table->integer('distance')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key')->unique();
                $table->text('setting_value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_allocations');
        Schema::dropIfExists('roster_data');
        Schema::dropIfExists('settings');
    }
};
