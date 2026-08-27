<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\JobLog;
use App\Models\MppMasterCategory;
use App\Models\MppRosterData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncRosterDataJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $startTime = now();
        $updatedCount = 0;
        $failedCount = 0;

        try {
            $employees = Employee::query()->activeOnly()->get();

            foreach ($employees as $employee) {
                try {
                    $masterCategory = MppMasterCategory::query()
                        ->where('jabatan', $employee->jabatan_ss6)
                        ->first();

                    $planMp = $masterCategory->plan_mp ?? 0;

                    MppRosterData::updateOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'nrp' => $employee->nrp,
                            'nama' => $employee->name,
                            'jabatan' => $employee->jabatan_ss6,
                            'unit' => $employee->unit,
                            'plan_mp' => $planMp,
                            'actual_mp' => $planMp,
                            'synced_at' => now(),
                        ]
                    );

                    $updatedCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to sync roster for employee {$employee->id}: " . $e->getMessage());
                    $failedCount++;
                }
            }

            $message = "Roster sync completed. Updated: {$updatedCount}, Failed: {$failedCount}";
            Log::info($message);

            JobLog::create([
                'job_name' => self::class,
                'status' => 'success',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Roster sync job failed: ' . $e->getMessage());

            JobLog::create([
                'job_name' => self::class,
                'status' => 'failed',
                'executed_at' => $startTime,
                'completed_at' => now(),
                'message' => 'Roster sync failed',
                'error_log' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
