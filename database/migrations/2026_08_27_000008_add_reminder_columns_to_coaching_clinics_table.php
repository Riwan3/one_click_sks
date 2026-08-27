<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coaching_clinics', function (Blueprint $table) {
            $table->string('coach_email')->nullable();
            $table->string('topik')->nullable();
            $table->integer('reminders_sent')->default(0);
            $table->dateTime('last_reminder_sent')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('coaching_clinics', function (Blueprint $table) {
            $table->dropColumn(['coach_email', 'topik', 'reminders_sent', 'last_reminder_sent']);
        });
    }
};
