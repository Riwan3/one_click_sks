<?php

namespace App\Jobs;

use App\Models\JobLog;
use App\Models\SkPembinaan;
use App\Models\SuratPeringatan;
use App\Models\SuratTeguran;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExpireDraftDocumentsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();
        $expiredCount = 0;

        try {
            $expiryDate = now()->subDays(30);

            $expiredSurat = SuratTeguran::query()
                ->where('status', 'draft')
                ->where('created_at', '<', $expiryDate)
                ->update(['status' => 'expired']);

            $expiredSuraratPeringatan = SuratPeringatan::query()
                ->where('status', 'draft')
                ->where('created_at', '<', $expiryDate)
                ->update(['status' => 'expired']);

            $expiredSk = SkPembinaan::query()
                ->where('status', 'draft')
                ->where('created_at', '<', $expiryDate)
                ->update(['status' => 'expired']);

            $expiredCount = $expiredSurat + $expiredSuraratPeringatan + $expiredSk;

            $message = "Draft documents expired: {$expiredCount}";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Draft expiry job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'Document expiry failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
