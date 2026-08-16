<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Data user yang sedang login — dummy dari session, ganti dengan auth()->user() begitu backend auth siap.
     */
    protected function currentUser(): array
    {
        return [
            'displayName' => session('dummy_user', 'Guest User'),
            'username' => session('dummy_username', '00000'),
            'jabatan' => session('dummy_jabatan', 'Data Analyst'),
            'unit' => session('dummy_unit', 'MASTER'),
            'foto' => null,
        ];
    }
}
