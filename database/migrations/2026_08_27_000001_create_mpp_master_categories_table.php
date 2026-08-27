<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_master_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->index();
            $table->string('kode')->unique();
            $table->integer('plan_mp')->default(100); // baseline dalam persen
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_master_categories');
    }
};
