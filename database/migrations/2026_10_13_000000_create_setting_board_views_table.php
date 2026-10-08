<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Log pembukaan papan Setting Manpower / Setting Fleet di Setting Board oleh pengguna non-admin (grafik dashboard One-Setting).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_board_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('board_type', 10); // manpower | fleet
            $table->date('work_date');
            $table->char('shift', 1); // D | N
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['board_type', 'viewed_at']);
            $table->index(['user_id', 'board_type', 'work_date', 'shift', 'viewed_at'], 'setting_board_views_dedupe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_board_views');
    }
};
