<?php

namespace App\Enums;

/** Status karyawan di tab Monitoring & tab Raport Evaluasi Kompetensi. */
enum AcademyMonitoringStatus: string
{
    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Pass = 'PASS';
    case Failed = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'Sedang Dikerjakan',
            self::Pass => 'Lulus',
            self::Failed => 'Tidak Lulus',
        };
    }

    public static function resolve(object $row): self
    {
        if (! $row->result_id) {
            return self::Pending;
        }
        if ($row->result_status === 'in_progress') {
            return self::InProgress;
        }
        if ($row->result_status === 'completed') {
            return $row->passed ? self::Pass : self::Failed;
        }

        return self::Pending;
    }
}
