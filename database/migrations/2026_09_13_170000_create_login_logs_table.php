<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('login_logs')) {
            Schema::create('login_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('username', 100)->index();
                $table->string('nama', 255)->nullable();
                $table->string('role', 50)->nullable();
                $table->string('status', 50)->default('SUCCESS'); // SUCCESS, FAILED_PASSWORD, ACCOUNT_LOCKED, ACCOUNT_INACTIVE, LOGOUT
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('reason', 255)->nullable();
                $table->timestamp('login_at')->nullable()->index();
                $table->timestamp('logout_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
