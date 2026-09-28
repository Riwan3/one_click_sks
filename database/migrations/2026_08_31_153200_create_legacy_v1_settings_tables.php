<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Unit Support Lokasi
        if (!Schema::hasTable('unit_support_lokasi')) {
            Schema::create('unit_support_lokasi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 5)->default('1');
                $table->string('unit', 50);
                $table->string('lokasi', 200)->default('');
                $table->string('keterangan', 255)->default('');
                $table->timestamp('created_at')->useCurrent();
                
                $table->unique(['tanggal', 'shift', 'unit'], 'uk_tgl_shift_unit');
            });
        }

        // 2. Tabel Posisi Dozer PC
        if (!Schema::hasTable('posisi_dozer_pc')) {
            Schema::create('posisi_dozer_pc', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 5)->default('1');
                $table->string('kategori', 20)->default('');
                $table->string('unit', 50);
                $table->string('unit_label', 80)->default('');
                $table->string('posisi', 200)->default('');
                $table->string('keterangan', 150)->default('');
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['tanggal', 'shift', 'unit'], 'uk_tgl_shift_unit_dozer');
            });
        }

        // 3. Tabel PW Speakup CP
        if (!Schema::hasTable('pw_speakup_cp')) {
            Schema::create('pw_speakup_cp', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 5)->default('1');
                $table->string('password', 200)->default('');
                $table->timestamps(); // automatically adds created_at and updated_at
                
                $table->unique(['tanggal', 'shift'], 'uk_tgl_shift_pw');
            });
        }

        // 4. (Optional) Tabel ICC GL Settings just in case it doesn't exist
        if (!Schema::hasTable('icc_gl_settings')) {
            Schema::create('icc_gl_settings', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 5)->default('1');
                $table->string('unit', 50);
                $table->string('front_pic', 100)->default('');
                $table->string('disposal_pic', 100)->default('');
                $table->timestamps();

                $table->unique(['tanggal', 'shift', 'unit'], 'uk_tgl_shift_unit_gl');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_support_lokasi');
        Schema::dropIfExists('posisi_dozer_pc');
        Schema::dropIfExists('pw_speakup_cp');
    }
};
