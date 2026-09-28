<?php

namespace App\Enums;

/**
 * Status karyawan di tab Monitoring Evaluasi Prosedur.
 * Presedensi (sama seperti v1): C&M (coaching belum selesai) > REMEDIAL > PASS/FAILED.
 */
enum ProcedureMonitoringStatus: string
{
    case Pass = 'PASS';
    case Failed = 'FAILED';
    case Remedial = 'REMEDIAL';
    case Coaching = 'COACHING';
    case Pending = 'PENDING';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'PASS',
            self::Failed => 'FAILED',
            self::Remedial => 'REMEDIAL',
            self::Coaching => 'C&M',
            self::Pending => 'PENDING',
        };
    }

    public static function resolve(object $row): self
    {
        if (! $row->evaluation_id) return self::Pending;
        if ($row->pending_coaching_count > 0) return self::Coaching;
        if ($row->remedial_count > 0) return self::Remedial;

        return $row->result_status === 'passed' ? self::Pass : self::Failed;
    }
}
