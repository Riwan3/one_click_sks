<?php

namespace App\Jobs;

use App\Models\CoachingClinic;
use App\Models\Employee;
use App\Models\JobLog;
use App\Models\MppDailyAdjustment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEmployeeStatusJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();
        $syncedCount = 0;

        try {
            $employees = Employee::query()->get();

            foreach ($employees as $employee) {
                try {
                    $wasActive = $employee->status_karyawan === 'active';

                    // Sync from external HR system would happen here
                    // For now, simulating the sync logic

                    $isNowActive = $employee->status_karyawan === 'active';

                    if ($wasActive && ! $isNowActive) {
                        CoachingClinic::query()
                            ->where('employee_id', $employee->id)
                            ->delete();

                        MppDailyAdjustment::query()
                            ->where('employee_id', $employee->id)
                            ->delete();

                        Log::info("Employee {$employee->nrp} marked as inactive, related records deleted");
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync employee {$employee->id}: " . $e->getMessage());
                }
            }

            $message = "Employee status sync completed. Synced: {$syncedCount}";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Employee status sync job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'Employee status sync failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
