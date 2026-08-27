<?php

namespace App\Jobs;

use App\Models\JobLog;
use App\Models\MppUnitPhoto;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DeleteExpiredPhotosJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();

        try {
            $expiryDate = now()->subDays(90);
            $deletedCount = MppUnitPhoto::query()
                ->where('created_at', '<', $expiryDate)
                ->delete();

            $message = "Photo cleanup completed. Deleted: {$deletedCount} photos";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Photo cleanup job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'Photo cleanup failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
