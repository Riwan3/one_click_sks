<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Loader di Master Unit hanya kode + kategori (default setting harian dibuang); hauler punya kode parkiran M/G/J.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_master_units', function (Blueprint $table) {
            $table->dropColumn(['default_group', 'default_area', 'default_front', 'default_refueling_time', 'default_distance']);
            $table->string('parking_code', 1)->nullable()->index()->after('category'); // M = Monte, G = Gibson, J = Jumbo Vessel
        });
    }

    public function down(): void
    {
        Schema::table('fleet_master_units', function (Blueprint $table) {
            $table->dropColumn('parking_code');
            $table->string('default_group', 100)->nullable();
            $table->string('default_area', 100)->nullable();
            $table->string('default_front', 100)->nullable();
            $table->string('default_refueling_time', 50)->nullable();
            $table->unsignedInteger('default_distance')->default(0);
        });
    }
};
