<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Kunci One-OPD > Pengajuan Grade: terbuka 8 jam setelah password otorisasi dimasukkan, per sesi login. */
class OpdLock
{
    private const SESSION_KEY = 'opd_unlocked_at';

    private const TTL_SECONDS = 28800;

    public static function isUnlocked(Request $request): bool
    {
        $unlockedAt = (int) $request->session()->get(self::SESSION_KEY, 0);

        return $unlockedAt > 0 && (time() - $unlockedAt) < self::TTL_SECONDS;
    }

    public static function unlock(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, time());
    }

    public static function lock(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
