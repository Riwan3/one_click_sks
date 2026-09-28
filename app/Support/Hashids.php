<?php

namespace App\Support;

class Hashids
{
    private const CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const ROUND_KEYS = [0x9E3779B9, 0x85EBCA6B, 0xC2B2AE35, 0x27D4EB2F];

    /**
     * Encode an integer ID into a 7-character URL-safe hashid.
     */
    public static function encode(int|string|null $id): ?string
    {
        if ($id === null || $id === '' || ! is_numeric($id)) {
            return null;
        }

        $val = (int) $id;
        if ($val <= 0 || $val > 2147483647) {
            return null;
        }

        $permuted = self::feistelPermute($val);

        // Convert permuted 32-bit integer to 6 base62 characters
        $base62 = self::toBase62($permuted, 6);

        // Checksum (7th char)
        $checksum = ($permuted * 31 + 17) % 62;
        if ($checksum < 0) {
            $checksum += 62;
        }

        return $base62 . self::CHARS[$checksum];
    }

    /**
     * Decode a hashid back into an integer ID.
     * Supports legacy numeric fallback (returns int if numeric).
     */
    public static function decode(mixed $hash): ?int
    {
        if ($hash === null || $hash === '') {
            return null;
        }

        if (is_int($hash)) {
            return $hash > 0 ? $hash : null;
        }

        if (! is_string($hash)) {
            return null;
        }

        $hash = trim($hash);

        // Fallback for raw numeric IDs (backward compatibility)
        if (ctype_digit($hash)) {
            return (int) $hash;
        }

        if (strlen($hash) !== 7 || ! ctype_alnum($hash)) {
            return null;
        }

        $base62Part = substr($hash, 0, 6);
        $checksumChar = $hash[6];

        $permuted = self::fromBase62($base62Part);
        if ($permuted === null) {
            return null;
        }

        // Verify checksum
        $expectedChecksum = ($permuted * 31 + 17) % 62;
        if ($expectedChecksum < 0) {
            $expectedChecksum += 62;
        }

        if ($checksumChar !== self::CHARS[$expectedChecksum]) {
            return null; // Tampered or invalid
        }

        $original = self::feistelInvert($permuted);

        return ($original > 0 && $original <= 2147483647) ? $original : null;
    }

    private static ?array $cachedKeys = null;

    private static function getRoundKeys(): array
    {
        if (self::$cachedKeys !== null) {
            return self::$cachedKeys;
        }

        $salt = (string) (config('app.key') ?: 'one_click_laravel_secret_hash_salt_2026');
        $hash = hash('sha256', $salt);

        return self::$cachedKeys = [
            hexdec(substr($hash, 0, 8)) & 0xFFFF,
            hexdec(substr($hash, 8, 8)) & 0xFFFF,
            hexdec(substr($hash, 16, 8)) & 0xFFFF,
            hexdec(substr($hash, 24, 8)) & 0xFFFF,
        ];
    }

    private static function feistelPermute(int $val): int
    {
        $l = ($val >> 16) & 0xFFFF;
        $r = $val & 0xFFFF;
        $keys = self::getRoundKeys();

        for ($i = 0; $i < 4; $i++) {
            $f = self::roundFunction($r, $keys[$i]);
            $nextR = ($l ^ $f) & 0xFFFF;
            $l = $r;
            $r = $nextR;
        }

        return (($l << 16) | $r) & 0xFFFFFFFF;
    }

    private static function feistelInvert(int $val): int
    {
        $l = ($val >> 16) & 0xFFFF;
        $r = $val & 0xFFFF;
        $keys = self::getRoundKeys();

        for ($i = 3; $i >= 0; $i--) {
            $f = self::roundFunction($l, $keys[$i]);
            $prevL = ($r ^ $f) & 0xFFFF;
            $r = $l;
            $l = $prevL;
        }

        return (($l << 16) | $r) & 0x7FFFFFFF;
    }

    private static function roundFunction(int $r, int $key): int
    {
        $v = ($r * ($key & 0xFFFF)) & 0xFFFFFFFF;
        $v = ($v ^ ($v >> 4) ^ ($v << 5)) & 0xFFFF;
        return $v;
    }

    private static function toBase62(int $num, int $length): string
    {
        $chars = self::CHARS;
        $res = '';
        $val = $num;

        for ($i = 0; $i < $length; $i++) {
            $rem = $val % 62;
            if ($rem < 0) {
                $rem += 62;
            }
            $res = $chars[$rem] . $res;
            $val = intdiv($val, 62);
        }

        return $res;
    }

    private static function fromBase62(string $str): ?int
    {
        $chars = self::CHARS;
        $len = strlen($str);
        $val = 0;

        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($chars, $str[$i]);
            if ($pos === false) {
                return null;
            }
            $val = ($val * 62) + $pos;
        }

        return $val & 0xFFFFFFFF;
    }
}
