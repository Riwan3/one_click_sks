<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Pimpinan = 'pimpinan';
    case GroupLeader = 'group leader';
    case Helper = 'helper';
    case Trafficman = 'trafficman';
    case Tamu = 'tamu';
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Pimpinan => 'Pimpinan',
            self::GroupLeader => 'Group Leader',
            self::Helper => 'Helper',
            self::Trafficman => 'Trafficman',
            self::Tamu => 'Tamu',
            self::Operator => 'Operator',
        };
    }

    /**
     * Tentukan role otomatis dari jabatan SS6 karyawan — aturan yang sama
     * dipakai fitur import user di v1 (lihat catatan modal Import User).
     */
    public static function fromJabatan(?string $jabatan): self
    {
        $j = strtoupper($jabatan ?? '');

        return match (true) {
            str_contains($j, 'ADMIN') => self::Admin,
            str_contains($j, 'TRAFFIC') => self::Trafficman,
            str_contains($j, 'HELPER') => self::Helper,
            str_contains($j, 'DEPT HEAD'), str_contains($j, 'SECTION HEAD') => self::Pimpinan,
            str_contains($j, 'GROUP LEADER'), str_contains($j, 'GL') => self::GroupLeader,
            default => self::Operator,
        };
    }
}
