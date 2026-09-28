<?php

namespace App\Enums;

enum ProcedureCertificateStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::ExpiringSoon => 'Expiring Soon',
            self::Expired => 'Expired',
            self::Revoked => 'Revoked',
        };
    }
}
