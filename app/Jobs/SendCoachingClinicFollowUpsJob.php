<?php

namespace App\Jobs;

use App\Models\CoachingClinic;
use App\Models\JobLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCoachingClinicFollowUpsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();
        $sentCount = 0;
        $skippedCount = 0;

        try {
            $clinics = CoachingClinic::query()
                ->where('status', 'draft')
                ->where('created_at', '<', now()->subDays(7))
                ->get();

            foreach ($clinics as $clinic) {
                try {
                    $reminderCount = $clinic->reminders_sent ?? 0;

                    if ($reminderCount >= 3) {
                        $skippedCount++;
                        continue;
                    }

                    if ($clinic->coach_email) {
                        Mail::send('emails.coaching-clinic-reminder', [
                            'clinic' => $clinic,
                        ], function ($message) use ($clinic) {
                            $message->to($clinic->coach_email)
                                ->subject("Reminder: Coaching Clinic '{$clinic->topik}' Menunggu Tindakan");
                        });

                        $clinic->increment('reminders_sent');
                        $clinic->update(['last_reminder_sent' => now()]);

                        $sentCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder for coaching clinic {$clinic->id}: " . $e->getMessage());
                }
            }

            $message = "Coaching clinic follow-ups sent: {$sentCount}, Skipped: {$skippedCount}";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Coaching clinic follow-up job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'Coaching clinic follow-ups failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
