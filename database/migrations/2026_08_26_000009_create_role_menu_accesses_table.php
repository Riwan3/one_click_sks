<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_menu_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('role', 30);
            $table->string('module_key', 30);
            $table->string('menu_key', 80);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['role', 'module_key', 'menu_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menu_accesses');
    }
};
