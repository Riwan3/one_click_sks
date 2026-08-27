<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('icc_dms_data')) {
            Schema::create('icc_dms_data', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 10)->default('1');
                $table->string('petugas1')->nullable();
                $table->string('petugas2')->nullable();
                $table->string('nama_operator')->nullable();
                $table->string('nrp')->nullable();
                $table->string('unit')->nullable();
                $table->string('area')->nullable();
                $table->string('waktu', 20)->nullable();
                $table->string('jenis_deviasi')->nullable();
                $table->integer('deviasi_ke')->nullable();
                $table->string('temuan')->nullable();
                $table->string('evidence_icc')->nullable();
                $table->string('followup_hasil')->nullable();
                $table->string('followup_hasil_2')->nullable();
                $table->string('note_intervensi')->nullable();
                $table->string('sumber')->nullable();
                $table->string('group_leader')->nullable();
                $table->string('status', 50)->default('Open');
                $table->text('remark')->nullable();
                $table->string('created_by')->nullable();
                $table->unsignedBigInteger('synced_adas_id')->nullable();
                $table->unsignedBigInteger('synced_fatigue_id')->nullable();
                $table->unsignedBigInteger('synced_smoking_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('icc_dms_petugas')) {
            Schema::create('icc_dms_petugas', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 10);
                $table->string('petugas1')->nullable();
                $table->string('petugas2')->nullable();
                $table->timestamps();
                $table->unique(['tanggal', 'shift']);
            });
        }

        if (!Schema::hasTable('icc_ba_signature')) {
            Schema::create('icc_ba_signature', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 10);
                $table->string('diketahui1')->nullable();
                $table->string('diketahui2')->nullable();
                $table->timestamps();
                $table->unique(['tanggal', 'shift']);
            });
        }

        if (!Schema::hasTable('icc_gl_settings')) {
            Schema::create('icc_gl_settings', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 10)->default('1');
                $table->string('unit');
                $table->string('front_pic')->nullable();
                $table->string('disposal_pic')->nullable();
                $table->timestamps();
                $table->unique(['tanggal', 'shift', 'unit']);
            });
        }

        if (!Schema::hasTable('fatigue_adas_alarm')) {
            Schema::create('fatigue_adas_alarm', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 20)->default('Shift 1');
                $table->string('nama_operator')->nullable();
                $table->string('nrp')->nullable();
                $table->string('unit')->nullable();
                $table->string('area')->nullable();
                $table->string('waktu', 20)->nullable();
                $table->string('jenis_deviasi')->nullable();
                $table->integer('deviasi_ke')->default(1);
                $table->text('intervensi')->nullable();
                $table->string('group_leader')->nullable();
                $table->string('status', 50)->default('Close');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fatigue_data')) {
            Schema::create('fatigue_data', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 20)->default('Shift 1');
                $table->string('nama_operator')->nullable();
                $table->string('nrp')->nullable();
                $table->string('unit')->nullable();
                $table->string('area')->nullable();
                $table->string('waktu', 20)->nullable();
                $table->string('jenis_deviasi')->default('Driver Fatigue');
                $table->integer('deviasi_ke')->default(1);
                $table->text('intervensi')->nullable();
                $table->string('group_leader')->nullable();
                $table->string('status', 50)->default('Close');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('smoking_deviasi')) {
            Schema::create('smoking_deviasi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('shift', 20)->default('Shift 1');
                $table->string('unit')->nullable();
                $table->string('area')->nullable();
                $table->string('nama_operator')->nullable();
                $table->string('nrp')->nullable();
                $table->string('evidence')->nullable();
                $table->string('fu')->nullable();
                $table->string('group_leader')->nullable();
                $table->string('status', 50)->default('Close');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('smoking_deviasi');
        Schema::dropIfExists('fatigue_data');
        Schema::dropIfExists('fatigue_adas_alarm');
        Schema::dropIfExists('icc_gl_settings');
        Schema::dropIfExists('icc_ba_signature');
        Schema::dropIfExists('icc_dms_petugas');
        Schema::dropIfExists('icc_dms_data');
    }
};
