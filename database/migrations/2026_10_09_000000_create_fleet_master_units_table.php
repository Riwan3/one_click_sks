<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Master Unit Setting Fleet: loader (excavator/wheel loader) & hauler (dump truck) yang boleh dipakai di setting harian.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_master_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('unit_type', 10)->index(); // loader | hauler
            $table->string('category', 120)->nullable(); // mis. "EXCAVATOR - 2000" (nama kategori di Master CSA Manpower)
            $table->unsignedInteger('source_unit_id')->nullable()->index(); // v2_mp_units.id bila ditarik dari master manpower
            $table->string('default_group', 100)->nullable(); // khusus loader: bawaan untuk setting harian
            $table->string('default_area', 100)->nullable();
            $table->string('default_front', 100)->nullable();
            $table->string('default_refueling_time', 50)->nullable();
            $table->unsignedInteger('default_distance')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_master_units');
    }
};
