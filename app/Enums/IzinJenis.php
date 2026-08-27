<?php

namespace App\Enums;

enum IzinJenis: string
{
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Emergency => 'Emergency',
        };
    }

    public function suratKode(): string
    {
        return match ($this) {
            self::Izin => 'SI',
            self::Sakit => 'SS',
            self::Emergency => 'SE',
        };
    }
}
