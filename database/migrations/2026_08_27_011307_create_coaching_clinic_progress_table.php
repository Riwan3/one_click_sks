<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coaching_clinic_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coaching_clinic_id')->constrained('coaching_clinics')->cascadeOnDelete();
            $table->foreignId('coaching_tahapan_id')->constrained('coaching_tahapans')->cascadeOnDelete();
            $table->unsignedSmallInteger('progress_pct')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['coaching_clinic_id', 'coaching_tahapan_id'], 'clinic_tahapan_unique');
            $table->index('coaching_clinic_id');
            $table->index('coaching_tahapan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_clinic_progress');
    }
};
