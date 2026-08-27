<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_unit_photos', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['OJK', 'Checkpoint'])->index();
            $table->string('unit_code')->index();
            $table->date('tanggal')->index();
            $table->enum('shift', ['D', 'N'])->default('D');
            $table->string('nrp')->nullable(); // operator NRP
            $table->string('nama')->nullable(); // operator name
            $table->string('url'); // photo file path or URL
            $table->json('watermark_data')->nullable(); // watermark metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis', 'tanggal', 'unit_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_unit_photos');
    }
};
