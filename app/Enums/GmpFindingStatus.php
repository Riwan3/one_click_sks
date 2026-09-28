<?php

namespace App\Enums;

/**
 * Status temuan GMP (v1). "Overdue" bukan status tersimpan: dihitung dari
 * status aktif (open / on_progress) yang deadline-nya sudah lewat.
 */
enum GmpFindingStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case OnProgress = 'on_progress';
    case WaitingVerification = 'waiting_verification';
    case Rework = 'rework';
    case Closed = 'closed';

    public const OVERDUE = 'overdue';

    /** Status yang bisa menjadi overdue. */
    public static function overdueEligible(): array
    {
        return [self::Open->value, self::OnProgress->value];
    }

    public static function labels(): array
    {
        return [
            self::Draft->value => 'Draft',
            self::Open->value => 'Open',
            self::OnProgress->value => 'On Progress',
            self::WaitingVerification->value => 'Waiting Verif',
            self::Rework->value => 'Rework',
            self::Closed->value => 'Closed',
            self::OVERDUE => 'Overdue',
        ];
    }
}
