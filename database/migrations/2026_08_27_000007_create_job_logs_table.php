<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_logs', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->enum('status', ['success', 'failed', 'pending']);
            $table->dateTime('executed_at');
            $table->dateTime('completed_at')->nullable();
            $table->text('message')->nullable();
            $table->longText('error_log')->nullable();
            $table->timestamps();

            $table->index(['job_name', 'executed_at']);
            $table->index(['status', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_logs');
    }
};
