<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

//
// Putaran roster dasar (D / N / OFF) berdasarkan Kode Off, sama persis dengan sheet HELP pada "PUTARAN ROSTER.xlsx":
//   sel = VLOOKUP( MOD(nomor_seri_tanggal_Excel + offset_kode, 14), tabel_putaran, 2 )
//   offset_kode : A1/B1 = 0, A14/B14 = 1, A13/B13 = 2, ... , A2/B2 = 13   (urutan header sheet HELP: A1, A14, A13, ..., A2)
//   tabel A     : indeks 1-7 = D, 8-13 = N, 0 = OFF
//   tabel B     : indeks 1-6 = N, 7 = OFF, 8-13 = D, 0 = D
// Dihitung (bukan tabel tanggal) sehingga berlaku untuk tanggal kapan pun, tidak berhenti di akhir data Excel.
// Hanya menghasilkan D/N/OFF. Cuti (CT/ID) datang dari MONCU; MCU dan lainnya diisi manual.

class RosterV2Rotation {
    const CYCLE = 14;
    const PATTERN_ROTASI = 'ROTASI';          // siang-malam sesuai Excel
    const PATTERN_FULL_SIANG = 'FULL_SIANG';  // tanggal OFF sama dengan Excel, hari N diganti D

    /** 'ROTASI' | 'FULL_SIANG'; kosong -> ROTASI; tidak dikenal -> null. */
    public static function normalizePattern($p) {
        $p = strtoupper(trim((string)$p));
        if ($p === '' || $p === 'ROTASI') return self::PATTERN_ROTASI;
        return $p === 'FULL_SIANG' ? self::PATTERN_FULL_SIANG : null;
    }

    /** Hari sejak 1899-12-30 (nomor seri tanggal Excel, sistem 1900). */
    public static function excelSerial($ymd) {
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$ymd, new DateTimeZone('UTC'));
        if (!$d || $d->format('Y-m-d') !== $ymd) return null;
        $base = new DateTimeImmutable('1899-12-30', new DateTimeZone('UTC'));
        return (int)$base->diff($d)->format('%r%a');
    }

    /** A1..A14 dan B1..B14. */
    public static function codes() {
        $out = [];
        foreach (['A', 'B'] as $g) for ($i = 1; $i <= 14; $i++) $out[] = $g . $i;
        return $out;
    }

    /** Normalisasi (trim + huruf besar). Kosong -> ''. Tidak valid -> null. */
    public static function normalize($code) {
        $c = strtoupper(trim((string)$code));
        if ($c === '') return '';
        return preg_match('/^[AB](?:[1-9]|1[0-4])$/', $c) ? $c : null;
    }

    private static function table($group) {
        $t = [];
        for ($i = 0; $i < self::CYCLE; $i++) {
            if ($group === 'A') $t[$i] = $i === 0 ? 'OFF' : ($i <= 7 ? 'D' : 'N');
            else                $t[$i] = $i === 0 ? 'D' : ($i <= 6 ? 'N' : ($i === 7 ? 'OFF' : 'D'));
        }
        return $t;
    }

    /** 'D' | 'N' | 'OFF' untuk kode dan tanggal (YYYY-MM-DD); null bila kode/tanggal tidak valid. */
    public static function shiftFor($code, $ymd, $pattern = self::PATTERN_ROTASI) {
        $c = self::normalize($code);
        if ($c === null || $c === '') return null;
        $serial = self::excelSerial($ymd);
        if ($serial === null) return null;
        $offset = (self::CYCLE + 1 - (int)substr($c, 1)) % self::CYCLE;   // A1 -> 0, A14 -> 1, ... A2 -> 13
        $idx    = ($serial + $offset) % self::CYCLE;
        if ($idx < 0) $idx += self::CYCLE;
        $s = self::table($c[0])[$idx];
        return ($pattern === self::PATTERN_FULL_SIANG && $s === 'N') ? 'D' : $s;
    }

    /** [tanggal => kode shift] untuk rentang inklusif. */
    public static function range($code, $from, $to, $pattern = self::PATTERN_ROTASI) {
        $out = [];
        if (self::normalize($code) === null || self::normalize($code) === '') return $out;
        for ($d = new DateTimeImmutable($from); $d->format('Y-m-d') <= $to; $d = $d->modify('+1 day')) {
            $ymd = $d->format('Y-m-d');
            $out[$ymd] = self::shiftFor($code, $ymd, $pattern);
        }
        return $out;
    }
}
