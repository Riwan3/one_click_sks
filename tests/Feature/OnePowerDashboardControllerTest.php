<?php

namespace Tests\Feature;

use App\Models\AttendanceDetail;
use App\Models\CoachingCounseling;
use App\Models\Employee;
use App\Models\OperatorUnfit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnePowerDashboardControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tdb'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_kehadiran_tab_computes_from_attendance_details(): void
    {
        foreach ([
            ['nrp' => 'DB1', 'name' => 'Karyawan Satu', 'code' => 'D'],
            ['nrp' => 'DB1', 'name' => 'Karyawan Satu', 'code' => 'S'],
            ['nrp' => 'DB2', 'name' => 'Karyawan Dua', 'code' => 'N'],
            ['nrp' => 'DB2', 'name' => 'Karyawan Dua', 'code' => 'A'],
        ] as $i => $data) {
            AttendanceDetail::create([
                'nrp' => $data['nrp'],
                'name' => $data['name'],
                'position' => 'OPERATOR',
                'tanggal' => sprintf('2030-09-%02d', $i + 1),
                'code' => $data['code'],
                'dept' => 'PRO',
            ]);
        }

        $this->actingAs($this->admin())
            ->get('/one-power?bulan=9&tahun=2030')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/Dashboard')
                ->where('attendance.stats.totalRecords', 4)
                ->where('attendance.stats.hadirCount', 2)
                ->where('attendance.stats.nonHadirCount', 2)
                ->where('attendance.stats.totalEmployees', 2)
            );
    }

    public function test_deviasi_tab_pulls_unfit_from_operator_unfit_table(): void
    {
        $employee = Employee::create([
            'nrp' => 'DEV'.random_int(10000, 99999),
            'name' => 'Karyawan Unfit',
            'lokasi_kerja' => 'SITE TEST',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'status_karyawan' => 'FIT',
        ]);

        OperatorUnfit::create([
            'nrp' => $employee->nrp,
            'nama' => $employee->name,
            'tgl_mulai_unfit' => now()->subDays(2),
        ]);

        $this->actingAs($this->admin())
            ->get('/one-power')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/Dashboard')
                ->where('deviasi.unfitTotal', 1)
                ->where('deviasi.unfitByLokasi.0.label', 'SITE TEST')
                ->where('deviasi.unfitList.0.nrp', $employee->nrp)
            );
    }

    public function test_coaching_tab_pulls_from_coaching_counselings_and_filters_by_search(): void
    {
        $keyword = 'CoachTest'.random_int(1000, 9999);

        $neverCoached = Employee::create([
            'nrp' => 'CC'.random_int(10000, 99999),
            'name' => $keyword.' Never',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'status_karyawan' => 'FIT',
        ]);

        $leastCoached = Employee::create([
            'nrp' => 'CC'.random_int(10000, 99999),
            'name' => $keyword.' Least',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'status_karyawan' => 'FIT',
        ]);

        $coach = Employee::create([
            'nrp' => 'CC'.random_int(10000, 99999),
            'name' => 'Coach Tester',
            'jabatan_ss6' => 'GROUP LEADER',
            'unit' => 'Produksi',
            'status_karyawan' => 'FIT',
        ]);

        CoachingCounseling::create(['employee_id' => $leastCoached->id, 'coach_employee_id' => $coach->id, 'tanggal' => '2030-03-15']);
        CoachingCounseling::create(['employee_id' => $leastCoached->id, 'coach_employee_id' => $coach->id, 'tanggal' => '2030-06-10']);

        $this->actingAs($this->admin())
            ->get("/one-power?tahun=2030&coaching_search={$keyword}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/Dashboard')
                ->where('coaching.tahun', 2030)
                ->where('coaching.totalCcTahun', 2)
                ->has('coaching.neverCoachedList', 1)
                ->where('coaching.neverCoachedList.0.nrp', $neverCoached->nrp)
                ->has('coaching.leastCoachedList', 1)
                ->where('coaching.leastCoachedList.0.nrp', $leastCoached->nrp)
                ->where('coaching.leastCoachedList.0.totalCc', 2)
            );
    }
}
