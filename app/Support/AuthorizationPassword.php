<?php

namespace App\Support;

/** Password otorisasi keamanan yang dipakai bersama One-Core dan One-OPD (Pengajuan Grade). */
class AuthorizationPassword
{
    private const PASSWORD = 'admin123';

    public static function matches(?string $input): bool
    {
        return hash_equals(self::PASSWORD, (string) $input);
    }
}
