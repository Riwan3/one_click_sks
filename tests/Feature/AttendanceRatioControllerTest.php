<?php

namespace Tests\Feature;

use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AttendanceRatioControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tar'.random_int(10000, 99999), 'role' => 'admin']);
    }

    private function seedAttendance(string $nrp, string $name, int $year, int $month, array $codes): void
    {
        foreach ($codes as $day => $code) {
            AttendanceDetail::create([
                'nrp' => $nrp,
                'name' => $name,
                'position' => 'OPERATOR',
                'tanggal' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                'code' => $code,
                'dept' => 'PRO',
            ]);
        }
    }

    public function test_monthly_atr_summary_computes_ratio_from_attendance_details(): void
    {
        $employee = Employee::create([
            'nrp' => 'ATR'.random_int(10000, 99999),
            'name' => 'Karyawan ATR',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'no_telepon' => '081234567890',
            'status_karyawan' => 'FIT',
        ]);

        $this->seedAttendance($employee->nrp, $employee->name, 2026, 9, [
            1 => 'D', 2 => 'D', 3 => 'N', 4 => 'S', 5 => 'A',
        ]);

        $this->actingAs($this->admin())
            ->get('/one-power/personal-data/attendance-ratio?bulan=9&tahun=2026&search='.$employee->nrp)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/AttendanceRatio/Index')
                ->where('items.0.nrp', $employee->nrp)
                ->where('items.0.d', 2)
                ->where('items.0.n', 1)
                ->where('items.0.s', 1)
                ->where('items.0.a', 1)
                ->where('items.0.atr', 60)
            );
    }

    public function test_quarterly_low_atr_toggle_flags_employee_below_threshold(): void
    {
        $employee = Employee::create([
            'nrp' => 'ATR'.random_int(10000, 99999),
            'name' => 'Karyawan Rendah',
            'jabatan_ss6' => 'OPERATOR PC',
            'unit' => 'Produksi',
            'no_telepon' => '081234567891',
            'status_karyawan' => 'FIT',
        ]);

        foreach ([7, 8, 9] as $month) {
            $codes = [];
            for ($d = 1; $d <= 29; $d++) {
                $codes[$d] = $d <= 10 ? 'D' : 'A';
            }
            $this->seedAttendance($employee->nrp, $employee->name, 2026, $month, $codes);
        }

        $this->actingAs($this->admin())
            ->get('/one-power/personal-data/attendance-ratio?filter_low_atr=1&bulan=9&tahun=2026&search='.$employee->nrp)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OnePower/AttendanceRatio/Index')
                ->where('filterLowAtr', true)
                ->where('items.0.nrp', $employee->nrp)
            );
    }

    public function test_admin_can_save_atr_threshold(): void
    {
        $this->actingAs($this->admin())
            ->post('/one-power/personal-data/attendance-ratio/threshold', ['atr_threshold' => 85])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('85', \App\Models\Setting::getValue('atr_threshold'));
    }

    public function test_operator_cannot_save_atr_threshold(): void
    {
        $operator = User::factory()->create(['username' => 'top'.random_int(10000, 99999), 'role' => 'operator']);

        $this->actingAs($operator)
            ->post('/one-power/personal-data/attendance-ratio/threshold', ['atr_threshold' => 50])
            ->assertRedirect('/one-power/personal-data/attendance-ratio');

        $this->assertNotSame('50', \App\Models\Setting::getValue('atr_threshold'));
    }
}
