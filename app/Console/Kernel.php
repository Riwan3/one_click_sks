<?php

namespace App\Console;

use App\Jobs\DeleteExpiredPhotosJob;
use App\Jobs\ExpireDraftDocumentsJob;
use App\Jobs\GenerateDailyMppSummaryJob;
use App\Jobs\SendCoachingClinicFollowUpsJob;
use App\Jobs\SyncEmployeeStatusJob;
use App\Jobs\SyncRosterDataJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Roster sync job - every day at 06:00
        $schedule->job(new SyncRosterDataJob)
            ->dailyAt('06:00')
            ->name('SyncRosterDataJob')
            ->onSuccess(function () {
                // Optional: Handle success
            })
            ->onFailure(function () {
                // Optional: Handle failure
            });

        // Photo cleanup - every Sunday at 00:00
        $schedule->job(new DeleteExpiredPhotosJob)
            ->weekly()
            ->sundays()
            ->at('00:00')
            ->name('DeleteExpiredPhotosJob');

        // Daily MPP summary - every day at 17:00
        $schedule->job(new GenerateDailyMppSummaryJob)
            ->dailyAt('17:00')
            ->name('GenerateDailyMppSummaryJob');

        // Coaching clinic follow-ups - every day at 09:00
        $schedule->job(new SendCoachingClinicFollowUpsJob)
            ->dailyAt('09:00')
            ->name('SendCoachingClinicFollowUpsJob');

        // Draft document expiry - every day at 23:00
        $schedule->job(new ExpireDraftDocumentsJob)
            ->dailyAt('23:00')
            ->name('ExpireDraftDocumentsJob');

        // Employee status sync - twice daily (08:00 and 16:00)
        $schedule->job(new SyncEmployeeStatusJob)
            ->twiceDaily(8, 16)
            ->name('SyncEmployeeStatusJob');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
