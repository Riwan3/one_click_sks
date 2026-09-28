<?php

namespace App\Enums;

enum ProcedureFollowupType: string
{
    case RemedialOnly = 'remedial_only';
    case Coaching = 'coaching';

    public function label(): string
    {
        return match ($this) {
            self::RemedialOnly => 'Remedial Langsung',
            self::Coaching => 'Coaching & Mentoring',
        };
    }
}
