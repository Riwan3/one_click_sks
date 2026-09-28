<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reward_reads')) {
            Schema::create('reward_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('seen_at')->useCurrent();
                $table->unique(['reward_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_reads');
    }
};
