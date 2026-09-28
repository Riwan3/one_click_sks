<?php

namespace App\Enums;

enum BestEmployeeCampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Terjadwal',
            self::Active => 'Aktif',
            self::Finished => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Badge warna Tailwind untuk status ini (amber theme, konsisten dgn Index.vue). */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            self::Scheduled => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
            self::Active => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300',
            self::Finished => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            self::Cancelled => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
        };
    }
}
