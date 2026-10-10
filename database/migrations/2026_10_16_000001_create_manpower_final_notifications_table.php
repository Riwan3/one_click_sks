<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan penempatan yang sudah diberitahukan ke operator saat Setting Manpower difinalkan.
 * Dipakai agar Finalkan ulang (setelah Reopen) hanya mengirim notifikasi ke yang penempatannya berubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manpower_final_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_id');
            $table->string('nrp', 30);
            $table->string('signature', 255); // unit|csa|pc yang terakhir diberitahukan
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['setting_id', 'nrp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manpower_final_notifications');
    }
};
