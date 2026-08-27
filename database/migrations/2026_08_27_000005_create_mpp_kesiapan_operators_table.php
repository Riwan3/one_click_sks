<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_kesiapan_operators', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('csa_code')->index(); // Critical System Area
            $table->integer('operator_count')->default(0); // total operators available
            $table->integer('ready_count')->default(0); // operators ready (equipment OK)
            $table->decimal('percentage', 5, 2)->default(0); // ready_count / operator_count * 100
            $table->enum('status', ['Green', 'Yellow', 'Red'])->default('Green'); // color coding
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'csa_code']);
            $table->unique(['tanggal', 'csa_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_kesiapan_operators');
    }
};
