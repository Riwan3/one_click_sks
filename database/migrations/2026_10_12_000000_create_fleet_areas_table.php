<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Area Setting Fleet harian (induk cluster). fleet_clusters.area tetap berupa teks nama area.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_areas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedTinyInteger('shift'); // 1 = siang (D), 2 = malam (N)
            $table->string('name', 100);
            $table->string('created_by', 50)->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'shift', 'name']);
            $table->index(['tanggal', 'shift']);
        });

        $this->backfillFromClusters();
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_areas');
    }

    /** Area lama (teks di fleet_clusters.area) dibuatkan barisnya menurut urutan ditambahkan (MIN id cluster), agar urutan tidak berubah. */
    private function backfillFromClusters(): void
    {
        $now = now();
        $rows = DB::table('fleet_clusters')
            ->whereNotNull('area')->whereRaw("TRIM(area) <> ''")
            ->groupBy('tanggal', 'shift', 'area')
            ->orderByRaw('MIN(id)')
            ->get(['tanggal', 'shift', DB::raw('MIN(TRIM(area)) as name')]);

        foreach ($rows as $row) {
            DB::table('fleet_areas')->insertOrIgnore([
                'tanggal' => $row->tanggal, 'shift' => $row->shift, 'name' => $row->name, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
};
