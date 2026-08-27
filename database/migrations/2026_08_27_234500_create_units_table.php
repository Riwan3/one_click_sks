<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->enum('category_type', ['staff', 'nonStaff'])->default('nonStaff')->index();
            $table->string('name')->index();
            $table->integer('jumlah_unit')->default(0);
            $table->decimal('mp27', 8, 2)->default(0);
            $table->decimal('mp29', 8, 2)->default(0);
            $table->text('patterns')->nullable();
            $table->decimal('index_pengawas', 8, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
