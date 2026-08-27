<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spdk_logs', function (Blueprint $table) {
            $table->id();
            $table->string('nrp');
            $table->enum('action', ['lookup', 'sync', 'validate']);
            $table->enum('status', ['success', 'failed', 'error']);
            $table->longText('response');
            $table->timestamps();

            $table->index(['nrp', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spdk_logs');
    }
};
