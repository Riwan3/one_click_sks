<?php

namespace App\Enums;

enum RoleScope: string
{
    case ControlCenter = 'control_center';
    case Site = 'site';

    public function label(): string
    {
        return match ($this) {
            self::ControlCenter => 'Control Center',
            self::Site => 'Site',
        };
    }
}
