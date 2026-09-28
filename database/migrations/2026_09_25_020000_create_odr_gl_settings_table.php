<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mapping Area & Unit → Front PIC / Disposal PIC per tanggal + shift (Setting GL v1)
        Schema::create('odr_gl_settings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('shift', 20)->default('');
            $table->string('area', 150)->default('');
            $table->string('unit', 50)->default('');
            $table->string('front_pic', 150)->default('');
            $table->string('disposal_pic', 150)->default('');
            $table->timestamps();
            $table->unique(['tanggal', 'shift', 'area', 'unit'], 'uk_tgl_shift_area_unit');
        });

        // Penanda assignment hasil sinkron Setting GL (diganti tiap parse ulang; assignment manual tidak disentuh)
        Schema::table('odr_assignments', function (Blueprint $table) {
            $table->boolean('gl_synced')->default(false)->after('high_risk');
        });
    }

    public function down(): void
    {
        Schema::table('odr_assignments', fn (Blueprint $table) => $table->dropColumn('gl_synced'));
        Schema::dropIfExists('odr_gl_settings');
    }
};
