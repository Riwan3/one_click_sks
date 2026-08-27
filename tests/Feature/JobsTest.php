<?php

namespace Tests\Feature;

use App\Jobs\DeleteExpiredPhotosJob;
use App\Jobs\ExpireDraftDocumentsJob;
use App\Jobs\GenerateDailyMppSummaryJob;
use App\Jobs\SendCoachingClinicFollowUpsJob;
use App\Jobs\SyncEmployeeStatusJob;
use App\Jobs\SyncRosterDataJob;
use App\Models\JobLog;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class JobsTest extends TestCase
{
    public function test_sync_roster_data_job_dispatches()
    {
        Bus::fake();

        dispatch(new SyncRosterDataJob());

        Bus::assertDispatched(SyncRosterDataJob::class);
    }

    public function test_delete_expired_photos_job_dispatches()
    {
        Bus::fake();

        dispatch(new DeleteExpiredPhotosJob());

        Bus::assertDispatched(DeleteExpiredPhotosJob::class);
    }

    public function test_generate_daily_mpp_summary_job_dispatches()
    {
        Bus::fake();

        dispatch(new GenerateDailyMppSummaryJob());

        Bus::assertDispatched(GenerateDailyMppSummaryJob::class);
    }

    public function test_send_coaching_clinic_follow_ups_job_dispatches()
    {
        Bus::fake();

        dispatch(new SendCoachingClinicFollowUpsJob());

        Bus::assertDispatched(SendCoachingClinicFollowUpsJob::class);
    }

    public function test_expire_draft_documents_job_dispatches()
    {
        Bus::fake();

        dispatch(new ExpireDraftDocumentsJob());

        Bus::assertDispatched(ExpireDraftDocumentsJob::class);
    }

    public function test_sync_employee_status_job_dispatches()
    {
        Bus::fake();

        dispatch(new SyncEmployeeStatusJob());

        Bus::assertDispatched(SyncEmployeeStatusJob::class);
    }

    public function test_job_log_is_created_on_execution()
    {
        // Clear existing logs
        JobLog::truncate();

        // Dispatch and execute job
        dispatch(new SyncRosterDataJob())->allOnConnection('sync');

        // Verify log was created
        $this->assertDatabaseHas('job_logs', [
            'job_name' => SyncRosterDataJob::class,
        ]);
    }
}
