<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Data user yang sedang login. Jabatan diambil dari Employee (data HR) kalau
     * akun tertaut ke employee, fallback ke kolom jabatan/unit milik User sendiri.
     */
    protected function currentUser(): array
    {
        $user = Auth::user()->loadMissing('employee');

        return [
            'displayName' => $user->name,
            'username' => $user->username,
            'jabatan' => $user->employee?->jabatan_ss6 ?? $user->jabatan,
            'unit' => $user->employee?->unit ?? $user->unit,
            'foto' => null,
        ];
    }

    /**
     * Jumlah notifikasi belum dibaca.
     */
    protected function notifCounts(): array
    {
        $userId = Auth::id();
        $unreadMessages = 0;
        if ($userId) {
            $unreadMessages = \App\Models\InternalMessage::where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
        }

        return [
            'signature' => 0,
            'notification' => 0,
            'message' => $unreadMessages,
        ];
    }
}
