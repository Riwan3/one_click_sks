<?php

namespace App\Enums;

enum IzinStatus: string
{
    case Awaiting = 'Awaiting Approval';
    case Approved = 'Approved';
    case Declined = 'Declined';
    case Cancelled = 'Cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Awaiting => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
            self::Declined => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
