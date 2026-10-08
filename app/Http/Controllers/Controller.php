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

        $jabatan = $user->employee?->jabatan_ss6 ?? $user->jabatan;
        $role = $user->role ?? '';
        $isOperator = strtolower((string) $role) === 'operator'
            || str_contains(strtoupper((string) ($jabatan ?? '')), 'OPERATOR')
            || str_contains(strtoupper((string) $role), 'OPERATOR');

        $fotoPath = $user->foto ?? $user->employee?->foto;
        $fotoUrl = $fotoPath ? asset('storage/' . $fotoPath) : null;

        return [
            'displayName' => $user->name,
            'username' => $user->username,
            'role' => $role,
            'jabatan' => $jabatan,
            'unit' => $user->employee?->unit ?? $user->unit,
            'foto' => $fotoUrl,
            'isOperator' => $isOperator,
            'isAdmin' => $user->isAdmin(),
            'canViewRosterV2' => $user->canViewRosterV2(),
        ];
    }

    /**
     * Jumlah notifikasi belum dibaca.
     */
    protected function notifCounts(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $unreadMessages = 0;
        if ($user) {
            if ($user->isAdmin()) {
                $adminIds = \App\Models\User::admins()->pluck('id');
                $unreadMessages = \App\Models\InternalMessage::whereIn('receiver_id', $adminIds)
                    ->whereNotIn('sender_id', $adminIds)
                    ->where('is_read', false)
                    ->count();
            } else {
                $unreadMessages = \App\Models\InternalMessage::where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        }

        return [
            'signature' => 0,
            'notification' => 0,
            'message' => $unreadMessages,
        ];
    }
}
