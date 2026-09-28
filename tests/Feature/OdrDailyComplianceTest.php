<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\OdrAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OdrDailyComplianceTest extends TestCase
{
    use DatabaseTransactions;

    private const DATE = '2031-02-10';

    private const BASE = '/one-reporting/daily';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'td'.substr(md5($role), 0, 6).random_int(1000, 9999)]);
    }

    private function makeAssignment(array $tasks = ['P5M', 'Cek APD'], array $done = [false, false]): OdrAssignment
    {
        return OdrAssignment::create([
            'tanggal' => self::DATE, 'shift' => 'Shift 1', 'area' => 'PIT DAILY', 'unit' => 'HD785', 'pengawas' => 'GL Daily',
            'aktivitas' => json_encode($tasks), 'task_done' => json_encode($done), 'status' => 'belum',
        ]);
    }

    public function test_admin_can_create_assignment_with_tasks(): void
    {
        $this->actingAs($this->user('admin'))->post(self::BASE.'/assignment', [
            'tanggal' => self::DATE, 'shift' => 'Shift 2', 'area' => 'PIT DAILY', 'unit' => 'EX-001', 'pengawas' => 'GL Baru',
            'aktivitas' => ['P5M', '', 'Housekeeping'],
        ])->assertRedirect(route('one-reporting.assignments', ['tanggal' => self::DATE, 'shift' => 'Shift 2']))
            ->assertSessionHas('success', 'Data assignment berhasil ditambahkan.');

        $assignment = OdrAssignment::where('pengawas', 'GL Baru')->firstOrFail();
        $this->assertSame(['P5M', 'Housekeeping'], $assignment->tasks());
    }

    public function test_assignment_requires_core_fields_and_max_five_tasks(): void
    {
        $this->actingAs($this->user('admin'))->post(self::BASE.'/assignment', [
            'tanggal' => self::DATE, 'shift' => 'Shift 1', 'aktivitas' => array_fill(0, 6, 'Task'),
        ])->assertSessionHasErrors(['area', 'unit', 'pengawas', 'aktivitas']);
    }

    public function test_toggling_tasks_updates_compliance_status(): void
    {
        $assignment = $this->makeAssignment();
        $admin = $this->user('admin');

        $this->actingAs($admin)->post(self::BASE."/{$assignment->id}/tasks", ['task_index' => 0, 'value' => true]);
        $this->assertSame('progress', $assignment->fresh()->status);

        $this->actingAs($admin)->post(self::BASE."/{$assignment->id}/tasks", ['task_index' => 1, 'value' => true]);
        $this->assertSame('selesai', $assignment->fresh()->status);
        $this->assertSame(100, $assignment->fresh()->compliance()['pct']);
    }

    public function test_daily_page_syncs_status_and_group_leader_is_read_only(): void
    {
        $assignment = $this->makeAssignment(['P5M', 'Cek APD'], [true, false]);
        $gl = $this->user('group-leader');

        $this->actingAs($gl)->get(self::BASE.'?tanggal='.self::DATE)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canManage', false)
                ->where('kpi.progress', 1)
                ->where('rows.0.compliance.pct', 50));

        $this->assertSame('progress', $assignment->fresh()->status);
        $this->actingAs($gl)->post(self::BASE."/{$assignment->id}/tasks", ['task_index' => 1, 'value' => true])->assertForbidden();
        $this->actingAs($gl)->get(self::BASE.'/assignment')->assertForbidden();
    }

    public function test_operator_cannot_open_daily_compliance(): void
    {
        $this->actingAs($this->user('operator'))->get(self::BASE)->assertForbidden();
    }

    public function test_import_csv_creates_assignments_and_skips_incomplete_rows(): void
    {
        $csv = "Tanggal,Area,Unit,Pengawas,Keterangan,Shift\n".self::DATE.",PIT IMPORT,DT-01,GL Import,,Shift 2\n".self::DATE.",,DT-02,,,\n";
        $file = UploadedFile::fake()->createWithContent('assignment.csv', $csv);

        $this->actingAs($this->user('admin'))->post(self::BASE.'/assignment/import', ['file_excel' => $file])
            ->assertSessionHas('success', '1 data berhasil diimport. 1 baris dilewati.');

        $this->assertDatabaseHas('odr_assignments', ['area' => 'PIT IMPORT', 'shift' => 'Shift 2', 'pengawas' => 'GL Import']);
    }
}
