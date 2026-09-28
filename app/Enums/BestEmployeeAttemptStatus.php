<?php

namespace App\Enums;

enum BestEmployeeAttemptStatus: string
{
    case BelumMulai = 'belum_mulai';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Expired = 'expired';
    case TimedOut = 'timed_out';
    case Reset = 'reset';

    public function label(): string
    {
        return match ($this) {
            self::BelumMulai => 'Belum Mulai',
            self::InProgress => 'Sedang Mengerjakan',
            self::Submitted => 'Selesai',
            self::Expired => 'Expired',
            self::TimedOut => 'Time Out',
            self::Reset => 'Reset',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BelumMulai => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
            self::InProgress => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            self::Submitted => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            self::Expired => 'bg-slate-800 text-white dark:bg-slate-700',
            self::TimedOut => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            self::Reset => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
        };
    }
}
