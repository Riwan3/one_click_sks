<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteExpiredPhotosJob;
use App\Jobs\ExpireDraftDocumentsJob;
use App\Jobs\GenerateDailyMppSummaryJob;
use App\Jobs\SendCoachingClinicFollowUpsJob;
use App\Jobs\SyncEmployeeStatusJob;
use App\Jobs\SyncRosterDataJob;
use App\Models\JobLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;

class JobMonitorController extends Controller
{
    private array $jobs = [
        'SyncRosterDataJob' => SyncRosterDataJob::class,
        'DeleteExpiredPhotosJob' => DeleteExpiredPhotosJob::class,
        'GenerateDailyMppSummaryJob' => GenerateDailyMppSummaryJob::class,
        'SendCoachingClinicFollowUpsJob' => SendCoachingClinicFollowUpsJob::class,
        'ExpireDraftDocumentsJob' => ExpireDraftDocumentsJob::class,
        'SyncEmployeeStatusJob' => SyncEmployeeStatusJob::class,
    ];

    public function index()
    {
        $jobStats = [];

        foreach ($this->jobs as $name => $class) {
            $lastRun = JobLog::where('job_name', $class)
                ->latest('executed_at')
                ->first();

            $jobStats[] = [
                'name' => $name,
                'class' => $class,
                'last_run' => $lastRun?->executed_at?->format('Y-m-d H:i:s') ?? 'Never',
                'status' => $lastRun?->status ?? 'pending',
                'message' => $lastRun?->message,
                'next_run' => $this->getNextRunTime($name),
            ];
        }

        return Inertia::render('Admin/JobMonitor', [
            'jobs' => $jobStats,
        ]);
    }

    public function manualTrigger(Request $request, string $jobName)
    {
        $this->authorize('isAdmin');

        if (! isset($this->jobs[$jobName])) {
            return back()->with('error', 'Job tidak ditemukan.');
        }

        try {
            dispatch(new $this->jobs[$jobName]());

            return back()->with('success', "Job '{$jobName}' berhasil ditrigger.");
        } catch (\Exception $e) {
            return back()->with('error', "Gagal menjalankan job: {$e->getMessage()}");
        }
    }

    public function history(Request $request)
    {
        $query = JobLog::query()->latest('executed_at');

        if ($request->has('job_name')) {
            $query->where('job_name', 'like', "%{$request->input('job_name')}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->paginate(25);

        return Inertia::render('Admin/JobHistory', [
            'logs' => $logs,
            'filters' => [
                'job_name' => $request->input('job_name'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    private function getNextRunTime(string $jobName): string
    {
        $schedules = [
            'SyncRosterDataJob' => '06:00',
            'DeleteExpiredPhotosJob' => 'Sunday 00:00',
            'GenerateDailyMppSummaryJob' => '17:00',
            'SendCoachingClinicFollowUpsJob' => '09:00',
            'ExpireDraftDocumentsJob' => '23:00',
            'SyncEmployeeStatusJob' => '08:00, 16:00',
        ];

        return $schedules[$jobName] ?? 'N/A';
    }
}
