<?php

namespace App\Enums;

enum OdrIncidentStatus: string
{
    case Open = 'open';
    case Progress = 'progress';
    case Selesai = 'selesai';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
