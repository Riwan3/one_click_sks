<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pendukung mesin MOR (port dari V1 database/quartal_operator_report):
 * timesheet & pembinaan sebagai input, mor_raport_bulanan sebagai hasil snapshot
 * yang dibaca raport "MOR Saya". Struktur disamakan dengan V1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qor_timesheet', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20);
            $table->string('nama', 150);
            $table->string('posisi', 100);
            $table->string('dept', 50)->nullable();
            $table->integer('tahun');
            $table->tinyInteger('quartal');
            // 0 = baris agregat quartal, 1-12 = baris bulanan.
            $table->tinyInteger('bulan')->default(0);
            $table->integer('timesheet_true')->default(0);
            $table->integer('timesheet_false')->default(0);
            $table->integer('total_timesheet')->default(0);
            $table->decimal('achievement', 9, 4)->default(0);
            $table->integer('point')->default(0);
            $table->string('kategori', 50)->nullable();
            $table->timestamps();

            $table->unique(['nrp', 'tahun', 'quartal', 'bulan'], 'uniq_nrp_periode_bulan');
        });

        Schema::create('qor_pembinaan', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20);
            $table->string('nama', 150)->nullable();
            $table->date('tanggal');
            $table->string('jenis', 150)->nullable();
            $table->string('kode', 20)->nullable();
            $table->string('detail', 255)->nullable();
            $table->string('no_surat', 100)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['tanggal', 'nrp'], 'idx_tanggal_nrp');
        });

        Schema::create('mor_raport_bulanan', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun');
            // M = bulanan, Q = quartal, Y = tahunan.
            $table->char('periode', 1)->default('M');
            // M: 1-12, Q: nomor quartal 1-4, Y: 0.
            $table->tinyInteger('bulan');
            $table->string('sheet', 20);
            $table->string('nrp', 20);
            $table->string('nama', 150)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->integer('ranking');
            $table->integer('jumlah');
            $table->decimal('total_poin', 7, 2);
            $table->string('prasyarat', 10);
            // IS = Istimewa, BS = Baik Sekali, kosong = tanpa kategori.
            $table->string('kategori', 5)->nullable();
            // JSON: inputs, poin, komponen{input,maks}, prasyarat_gagal, member_search, produksi.
            $table->mediumText('detail')->nullable();
            $table->dateTime('computed_at');

            $table->unique(['tahun', 'periode', 'bulan', 'sheet', 'nrp'], 'uniq_periode_nomor_sheet_nrp');
            $table->index(['nrp', 'tahun', 'bulan'], 'idx_nrp_periode');
        });

        // V1 menyimpan PDTY per bulan juga (bulan 0 = agregat quartal).
        Schema::table('qor_pdty', function (Blueprint $table) {
            $table->tinyInteger('bulan')->default(0)->after('quartal');
            $table->dropUnique('uniq_nrp_period_kat');
            $table->unique(['nrp', 'tahun', 'quartal', 'bulan', 'kategori'], 'uniq_nrp_periode_bulan_kategori');
        });
    }

    public function down(): void
    {
        Schema::table('qor_pdty', function (Blueprint $table) {
            $table->dropUnique('uniq_nrp_periode_bulan_kategori');
            $table->dropColumn('bulan');
            $table->unique(['nrp', 'tahun', 'quartal', 'kategori'], 'uniq_nrp_period_kat');
        });

        Schema::dropIfExists('mor_raport_bulanan');
        Schema::dropIfExists('qor_pembinaan');
        Schema::dropIfExists('qor_timesheet');
    }
};
