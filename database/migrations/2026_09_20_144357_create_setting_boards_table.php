<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_boards', function (Blueprint $table) {
            $table->id();
            $table->string('site', 50)->default('ADW');
            $table->date('tanggal');
            $table->string('shift', 10)->default('D'); // D = Day, N = Night
            $table->string('type', 20); // 'manpower' or 'fleet'
            $table->string('image_path');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['site', 'tanggal', 'shift', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_boards');
    }
};
