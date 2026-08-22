<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Fit = 'FIT';
    case Unfit = 'UNFIT';
    case Inactive = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::Fit => 'Fit',
            self::Unfit => 'Unfit',
            self::Inactive => 'Non-Aktif',
        };
    }
}
