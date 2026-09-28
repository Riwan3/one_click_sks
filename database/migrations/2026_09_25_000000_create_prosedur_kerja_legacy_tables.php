<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('prosedur_categories')) {
            Schema::create('prosedur_categories', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 100)->unique();
                $table->string('name', 255);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('prosedur_types')) {
            Schema::create('prosedur_types', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 100)->unique();
                $table->string('name', 255);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('prosedur_kerja')) {
            Schema::create('prosedur_kerja', function (Blueprint $table) {
                $table->id();
                $table->string('jenis', 100);
                $table->string('kode_dokumen', 100);
                $table->string('judul', 255);
                $table->date('tanggal')->nullable();
                $table->string('file_dokumen', 255)->nullable();
                $table->string('dibuat_oleh', 150)->nullable();
                $table->string('jabatan_dibuat', 150)->nullable();
                $table->date('tanggal_dibuat')->nullable();
                $table->longText('ttd_dibuat')->nullable();
                $table->string('ditinjau_oleh', 150)->nullable();
                $table->string('jabatan_ditinjau', 150)->nullable();
                $table->date('tanggal_ditinjau')->nullable();
                $table->longText('ttd_ditinjau')->nullable();
                $table->string('disetujui_oleh', 150)->nullable();
                $table->string('jabatan_disetujui', 150)->nullable();
                $table->date('tanggal_disetujui')->nullable();
                $table->longText('ttd_disetujui')->nullable();
                $table->enum('status', ['draft', 'aktif', 'arsip'])->default('aktif');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (!Schema::hasTable('prosedur_category_docs')) {
            Schema::create('prosedur_category_docs', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('prosedur_id');
                $table->timestamp('created_at')->useCurrent();
                $table->primary(['category_id', 'prosedur_id']);
                $table->index('prosedur_id', 'idx_prosedur');
            });
        }

        // Copy existing data from oneclick database if available
        try {
            $hasOneClick = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'oneclick'");
            if (!empty($hasOneClick)) {
                if (DB::table('prosedur_categories')->count() === 0) {
                    DB::statement("INSERT IGNORE INTO prosedur_categories (id, slug, name, created_at) SELECT id, slug, name, created_at FROM oneclick.prosedur_categories");
                }
                if (DB::table('prosedur_types')->count() === 0) {
                    DB::statement("INSERT IGNORE INTO prosedur_types (id, slug, name, created_at) SELECT id, slug, name, created_at FROM oneclick.prosedur_types");
                }
                if (DB::table('prosedur_kerja')->count() === 0) {
                    DB::statement("INSERT IGNORE INTO prosedur_kerja (id, jenis, kode_dokumen, judul, tanggal, file_dokumen, dibuat_oleh, jabatan_dibuat, tanggal_dibuat, ttd_dibuat, ditinjau_oleh, jabatan_ditinjau, tanggal_ditinjau, ttd_ditinjau, disetujui_oleh, jabatan_disetujui, tanggal_disetujui, ttd_disetujui, status, created_by, created_at, updated_at) SELECT id, jenis, kode_dokumen, judul, tanggal, file_dokumen, dibuat_oleh, jabatan_dibuat, tanggal_dibuat, ttd_dibuat, ditinjau_oleh, jabatan_ditinjau, tanggal_ditinjau, ttd_ditinjau, disetujui_oleh, jabatan_disetujui, tanggal_disetujui, ttd_disetujui, status, created_by, created_at, updated_at FROM oneclick.prosedur_kerja");
                }
                if (DB::table('prosedur_category_docs')->count() === 0) {
                    DB::statement("INSERT IGNORE INTO prosedur_category_docs (category_id, prosedur_id, created_at) SELECT category_id, prosedur_id, created_at FROM oneclick.prosedur_category_docs");
                }
            }
        } catch (\Throwable $e) {
            // Silently proceed if cross-db copy is restricted
        }

        // Ensure default types exist if still empty
        if (DB::table('prosedur_types')->count() === 0) {
            DB::table('prosedur_types')->insertOrIgnore([
                ['slug' => 'sop', 'name' => 'SOP'],
                ['slug' => 'ik', 'name' => 'INSTRUKSI KERJA'],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prosedur_category_docs');
        Schema::dropIfExists('prosedur_kerja');
        Schema::dropIfExists('prosedur_types');
        Schema::dropIfExists('prosedur_categories');
    }
};
