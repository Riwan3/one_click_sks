<?php

namespace App\Jobs;

use App\Models\JobLog;
use App\Models\MppDailyAdjustment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateDailyMppSummaryJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();

        try {
            $today = now()->startOfDay();

            $summary = MppDailyAdjustment::query()
                ->whereDate('created_at', $today)
                ->selectRaw('departemen, COUNT(*) as count, SUM(CAST(plan_mp AS UNSIGNED)) as total_plan, SUM(CAST(actual_mp AS UNSIGNED)) as total_actual')
                ->groupBy('departemen')
                ->get();

            $managementUsers = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', 'pimpinan'))
                ->get();

            foreach ($managementUsers as $user) {
                try {
                    Mail::send('emails.mpp-daily-summary', [
                        'summary' => $summary,
                        'date' => $today,
                        'user' => $user,
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Daily MPP Summary - ' . now()->format('Y-m-d'));
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to send MPP summary email to {$user->email}: " . $e->getMessage());
                }
            }

            $message = "Daily MPP summary generated and sent to " . count($managementUsers) . " management users";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('MPP summary job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'MPP summary generation failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
