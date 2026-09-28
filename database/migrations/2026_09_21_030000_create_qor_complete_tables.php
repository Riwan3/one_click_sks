<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Target By Distance
        if (!Schema::hasTable('target_by_distance')) {
            Schema::create('target_by_distance', function (Blueprint $table) {
                $table->id();
                $table->enum('kategori', ['COAL', 'OB']);
                $table->string('kombinasi', 30);
                $table->decimal('jarak_min', 12, 2);
                $table->decimal('target_pdty', 10, 4);
                $table->timestamps();

                $table->index(['kategori', 'kombinasi', 'jarak_min'], 'idx_lookup');
            });
        }

        // 2. Budget Fuel
        if (!Schema::hasTable('qor_budget_fuel')) {
            Schema::create('qor_budget_fuel', function (Blueprint $table) {
                $table->id();
                $table->string('versality', 100)->unique();
                $table->decimal('vol_target', 8, 2);
                $table->timestamps();
            });
        }

        // 3. SAP HM
        if (!Schema::hasTable('qor_sap_hm')) {
            Schema::create('qor_sap_hm', function (Blueprint $table) {
                $table->id();
                $table->string('measurement_doc', 50)->nullable();
                $table->string('nrp', 20);
                $table->string('nama', 150)->nullable();
                $table->string('kode_unit', 50)->nullable();
                $table->string('alat_muat', 50)->nullable();
                $table->date('tanggal');
                $table->string('operation', 20)->nullable();
                $table->string('proses', 20)->nullable();
                $table->decimal('hour_time', 8, 2)->default(0);
                $table->string('aktivitas', 10)->nullable();
                $table->decimal('hour_actv', 8, 2)->default(0);
                $table->string('kode_unit_2', 50)->nullable();
                $table->timestamps();

                $table->index(['nrp', 'tanggal']);
                $table->index('tanggal');
            });
        }

        // 4. ATR Attendance
        if (!Schema::hasTable('qor_atr_attendance')) {
            Schema::create('qor_atr_attendance', function (Blueprint $table) {
                $table->id();
                $table->string('nrp', 20);
                $table->string('nama', 150)->nullable();
                $table->string('posisi', 100)->nullable();
                $table->date('tanggal');
                $table->string('code', 10)->nullable();
                $table->string('dept', 50)->nullable();
                $table->string('company', 50)->nullable();
                $table->timestamps();

                $table->index(['nrp', 'tanggal']);
                $table->index('tanggal');
            });
        }

        // 5. Update QOR DPR to support full fields
        if (Schema::hasTable('qor_dpr')) {
            Schema::table('qor_dpr', function (Blueprint $table) {
                if (!Schema::hasColumn('qor_dpr', 'nrp')) {
                    $table->string('nrp', 20)->nullable()->after('employee_id');
                }
                if (!Schema::hasColumn('qor_dpr', 'nama')) {
                    $table->string('nama', 150)->nullable()->after('nrp');
                }
                if (!Schema::hasColumn('qor_dpr', 'source')) {
                    $table->string('source', 20)->default('OB')->after('total_bcm');
                }
                if (!Schema::hasColumn('qor_dpr', 'total_ton')) {
                    $table->decimal('total_ton', 12, 2)->nullable()->after('source');
                }
                if (!Schema::hasColumn('qor_dpr', 'distance')) {
                    $table->decimal('distance', 12, 2)->nullable()->after('total_ton');
                }
                if (!Schema::hasColumn('qor_dpr', 'kode_unit_2')) {
                    $table->string('kode_unit_2', 50)->nullable()->after('distance');
                }
            });
        }

        // 6. QOR PDTY
        if (!Schema::hasTable('qor_pdty')) {
            Schema::create('qor_pdty', function (Blueprint $table) {
                $table->id();
                $table->string('nrp', 20);
                $table->string('nama', 150)->nullable();
                $table->integer('tahun');
                $table->tinyInteger('quartal');
                $table->enum('kategori', ['COAL', 'OB']);

                // HM
                $table->decimal('hm_hd785_395', 10, 2)->nullable();
                $table->decimal('hm_c777_395', 10, 2)->nullable();
                $table->decimal('hm_hd785_1250', 10, 2)->nullable();
                $table->decimal('hm_hd785_jumbo', 10, 2)->nullable();
                $table->decimal('hm_c777_1250', 10, 2)->nullable();
                $table->decimal('hm_hd785_2000', 10, 2)->nullable();
                $table->decimal('hm_c777_2000', 10, 2)->nullable();
                $table->decimal('hm_e1250', 10, 2)->nullable();
                $table->decimal('hm_e6108', 10, 2)->nullable();
                $table->decimal('hm_e395', 10, 2)->nullable();
                $table->decimal('hm_e2000', 10, 2)->nullable();

                // ACT
                $table->decimal('act_hd785_395', 14, 4)->nullable();
                $table->decimal('act_c777_395', 14, 4)->nullable();
                $table->decimal('act_hd785_1250', 14, 4)->nullable();
                $table->decimal('act_hd785_jumbo', 14, 4)->nullable();
                $table->decimal('act_c777_1250', 14, 4)->nullable();
                $table->decimal('act_hd785_2000', 14, 4)->nullable();
                $table->decimal('act_c777_2000', 14, 4)->nullable();
                $table->decimal('act_e1250', 14, 4)->nullable();
                $table->decimal('act_e6108', 14, 4)->nullable();
                $table->decimal('act_e395', 14, 4)->nullable();
                $table->decimal('act_e2000', 14, 4)->nullable();

                // PDTY
                $table->decimal('pdty_hd785_395', 10, 4)->nullable();
                $table->decimal('pdty_c777_395', 10, 4)->nullable();
                $table->decimal('pdty_hd785_1250', 10, 4)->nullable();
                $table->decimal('pdty_hd785_jumbo', 10, 4)->nullable();
                $table->decimal('pdty_c777_1250', 10, 4)->nullable();
                $table->decimal('pdty_hd785_2000', 10, 4)->nullable();
                $table->decimal('pdty_c777_2000', 10, 4)->nullable();
                $table->decimal('pdty_e1250', 10, 4)->nullable();
                $table->decimal('pdty_e6108', 10, 4)->nullable();
                $table->decimal('pdty_e395', 10, 4)->nullable();
                $table->decimal('pdty_e2000', 10, 4)->nullable();

                // TARGET
                $table->decimal('target_hd785_395', 10, 4)->nullable();
                $table->decimal('target_c777_395', 10, 4)->nullable();
                $table->decimal('target_hd785_1250', 10, 4)->nullable();
                $table->decimal('target_hd785_jumbo', 10, 4)->nullable();
                $table->decimal('target_c777_1250', 10, 4)->nullable();
                $table->decimal('target_hd785_2000', 10, 4)->nullable();
                $table->decimal('target_c777_2000', 10, 4)->nullable();
                $table->decimal('target_e1250', 10, 4)->nullable();
                $table->decimal('target_e6108', 10, 4)->nullable();
                $table->decimal('target_e395', 10, 4)->nullable();
                $table->decimal('target_e2000', 10, 4)->nullable();

                // BUCKETS & SUMMARY
                $table->decimal('dist_bucket_395', 12, 2)->nullable();
                $table->decimal('dist_bucket_1250', 12, 2)->nullable();
                $table->decimal('dist_bucket_2000', 12, 2)->nullable();
                $table->decimal('hm_total', 10, 2)->nullable();
                $table->decimal('sumproduct_target', 10, 4)->nullable();
                $table->decimal('sumproduct_act', 10, 4)->nullable();
                $table->decimal('achievement_pct', 8, 4)->nullable();
                $table->decimal('achievement_combined', 8, 4)->nullable();
                $table->text('target_unresolved')->nullable();
                $table->timestamps();

                $table->unique(['nrp', 'tahun', 'quartal', 'kategori'], 'uniq_nrp_period_kat');
                $table->index(['tahun', 'quartal']);
            });
        }

        // 7. MOR Ranking
        if (!Schema::hasTable('mor_ranking')) {
            Schema::create('mor_ranking', function (Blueprint $table) {
                $table->id();
                $table->string('nrp', 20);
                $table->string('nama', 150)->nullable();
                $table->integer('tahun');
                $table->tinyInteger('quartal');
                $table->string('jabatan', 150)->nullable();
                $table->string('kategori', 50)->nullable();
                $table->string('grup', 10)->nullable();
                $table->decimal('atr_persen', 6, 2)->nullable();
                $table->decimal('atr_point', 6, 2)->nullable();
                $table->decimal('ts_persen', 6, 2)->nullable();
                $table->decimal('ts_point', 6, 2)->nullable();
                $table->decimal('hm_persen', 8, 2)->nullable();
                $table->decimal('hm_point', 6, 2)->nullable();
                $table->decimal('pdty_persen', 8, 4)->nullable();
                $table->decimal('pdty_point', 6, 2)->nullable();
                $table->decimal('fuel_point', 6, 2)->nullable();
                $table->decimal('pembinaan_point', 6, 2)->nullable();
                $table->decimal('total_poin', 7, 2)->nullable();
                $table->integer('ranking')->nullable();
                $table->boolean('fuel_pending')->default(true);
                $table->boolean('pembinaan_pending')->default(true);
                $table->boolean('grup_unmapped')->default(false);
                $table->timestamps();

                $table->unique(['nrp', 'tahun', 'quartal'], 'uniq_nrp_periode');
                $table->index(['tahun', 'quartal']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mor_ranking');
        Schema::dropIfExists('qor_pdty');
        Schema::dropIfExists('qor_atr_attendance');
        Schema::dropIfExists('qor_sap_hm');
        Schema::dropIfExists('qor_budget_fuel');
        Schema::dropIfExists('target_by_distance');
    }
};
