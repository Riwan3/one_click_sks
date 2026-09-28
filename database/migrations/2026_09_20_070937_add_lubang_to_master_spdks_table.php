<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_spdks', function (Blueprint $table) {
            $table->unsignedSmallInteger('lubang')->default(0)->after('sanksi');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        $spdks = DB::table('master_spdks')->get();
        foreach ($spdks as $spdk) {
            if ($spdk->sanksi && preg_match('/(\d+)\s*lubang/i', $spdk->sanksi, $m)) {
                DB::table('master_spdks')->where('id', $spdk->id)->update([
                    'lubang' => (int) $m[1],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('master_spdks', function (Blueprint $table) {
            $table->dropColumn(['lubang', 'updated_at']);
        });
    }
};
