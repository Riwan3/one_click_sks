<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V1 parity note (ONECLICK V1 `attendance_data` table / AttendanceRepository.php):
     * V1 stores one row per employee per month with 15 status-code count columns.
     * Only 5 of the 15 codes have a documented meaning anywhere in V1 (view filter
     * dropdown at views/database/attendance/index.php): d = Shift Siang, n = Shift
     * Malam, s = Sakit, i = Izin, a = Alfa (a.k.a. Alpa/tanpa keterangan). The other
     * 10 codes (io, nc, l, t, o, k, p, r, h, nr) appear only as bare column headers
     * in V1 — no legend/tooltip/comment anywhere in the V1 codebase explains them.
     * They are kept here for structural parity (matching V1's import template
     * layout) with a "meaning undocumented in V1 source" comment on each; confirm
     * the real definitions with HR / the original spec before relying on them.
     *
     * This is a NEW, additive table — it intentionally does not touch/replace the
     * existing `attendances` table (daily per-employee log), which DashboardService
     * still depends on for the OnePower Dashboard "Kehadiran" tab.
     */
    public function up(): void
    {
        Schema::create('attendance_monthly_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan'); // 1-12
            $table->unsignedSmallInteger('tahun');

            // Snapshot fields captured straight from the import row (V1 does not
            // resolve these from the employees master during import).
            $table->string('dept', 100)->nullable();
            $table->string('jabatan', 150)->nullable();
            $table->string('posisi', 150)->nullable();
            $table->string('comp', 50)->nullable();

            // --- Shift codes (V1 filter label) ---
            $table->unsignedSmallInteger('d')->default(0); // Shift Siang
            $table->unsignedSmallInteger('n')->default(0); // Shift Malam

            // --- Keterangan Kehadiran codes ---
            $table->unsignedSmallInteger('io')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('nc')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('l')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('t')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('o')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('s')->default(0); // Sakit
            $table->unsignedSmallInteger('i')->default(0); // Izin
            $table->unsignedSmallInteger('a')->default(0); // Alfa / Alpa (tanpa keterangan)
            $table->unsignedSmallInteger('k')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('p')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('r')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('h')->default(0); // meaning undocumented in V1 source
            $table->unsignedSmallInteger('nr')->default(0); // meaning undocumented in V1 source

            // V1 stores ATR as a free-text/imported value (varchar, sometimes with a
            // "%" suffix), not a value computed from the codes above within this
            // table's own repository. We store it as a decimal here (stripped of
            // "%") and fall back to a computed accessor only when it is null.
            $table->decimal('atr_percentage', 6, 2)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // V1 dedups per nrp + month + year at the application level (no DB
            // constraint). We enforce it properly here since this is a new table.
            $table->unique(['employee_id', 'tahun', 'bulan']);
            $table->index(['tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_monthly_rosters');
    }
};
