<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_daily_adjustments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->enum('shift', ['D', 'N'])->default('D'); // D = day, N = night
            $table->string('kategori')->index(); // refers to mpp_master_categories.kode
            $table->integer('jumlah')->default(0); // total plan MP
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alfa')->default(0);
            $table->integer('training')->default(0);
            $table->integer('induksi')->default(0);
            $table->integer('tidak_checkin')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'shift', 'kategori']);
            $table->unique(['tanggal', 'shift', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_daily_adjustments');
    }
};
