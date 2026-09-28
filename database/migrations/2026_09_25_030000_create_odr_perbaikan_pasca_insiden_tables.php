<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odr_perbaikan_pasca_insiden', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_insiden')->index();
            $table->string('judul_insiden', 200);
            $table->string('area', 150)->nullable()->index();
            $table->text('deskripsi_insiden')->nullable();
            $table->date('target_selesai')->nullable();
            $table->enum('status', ['open', 'progress', 'selesai'])->default('open')->index();
            $table->timestamps();
        });

        // Tindakan per insiden: pencegahan (preventif) atau mitigasi
        Schema::create('odr_perbaikan_tindakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insiden_id')->constrained('odr_perbaikan_pasca_insiden')->cascadeOnDelete();
            $table->enum('jenis', ['pencegahan', 'mitigasi']);
            $table->text('uraian_tindakan');
            $table->string('pic', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('odr_perbaikan_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tindakan_id')->constrained('odr_perbaikan_tindakan')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('uploaded_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odr_perbaikan_evidence');
        Schema::dropIfExists('odr_perbaikan_tindakan');
        Schema::dropIfExists('odr_perbaikan_pasca_insiden');
    }
};
