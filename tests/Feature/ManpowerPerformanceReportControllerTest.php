<?php

namespace Tests\Feature;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\HmData;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ManpowerPerformanceReportControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tmpr'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_monthly_breakdown_pulls_atr_from_attendance_and_hm_from_hm_data(): void
    {
        $employee = Employee::create([
            'nrp' => 'MPR'.random_int(10000, 99999),
            'name' => 'Karyawan MPR',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'status_karyawan' => 'FIT',
        ]);

        foreach ([1 => 'D', 2 => 'D', 3 => 'N', 4 => 'S'] as $day => $code) {
            AttendanceDetail::create([
                'nrp' => $employee->nrp,
                'name' => $employee->name,
                'position' => 'OPERATOR',
                'tanggal' => sprintf('2026-09-%02d', $day),
                'code' => $code,
                'dept' => 'PRO',
            ]);
        }

        HmData::create([
            'employee_id' => $employee->id,
            'tanggal' => '2026-09-05',
            'shift' => 'all',
            'unit' => 'Produksi',
            'hm_start' => 100,
            'hm_stop' => 150,
            'hm_diff' => 50,
        ]);

        $this->actingAs($this->admin())
            ->get("/one-power/personal-data/mpr?nrp={$employee->nrp}&months_back=1")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/ManpowerPerformanceReport/Index')
                ->where('rows.0.atr', 75)
                ->where('rows.0.sakit', 1)
                ->where('rows.0.hm', 50)
            );
    }
}
