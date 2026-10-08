<?php

namespace Tests\Feature\OneSetting;

use App\Repositories\ManpowerV201SettingRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ManpowerSs6CodesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ss6_codes_come_from_attendance_details(): void
    {
        DB::table('attendance_details')->insert([
            'nrp' => '9990001', 'name' => 'Tes SS6', 'tanggal' => '2031-02-03',
            'jam_in' => '05:55', 'job' => 'DT', 'code' => 'DR', 'device_count' => 0,
        ]);

        $codes = app(ManpowerV201SettingRepository::class)->ss6Codes('2031-02-03', ['9990001', '9990002']);

        $this->assertSame(['9990001' => ['code' => 'DR', 'job' => 'DT', 'jam_in' => '05:55']], $codes);
    }

    public function test_ss6_codes_empty_when_no_attendance_on_date(): void
    {
        $this->assertSame([], app(ManpowerV201SettingRepository::class)->ss6Codes('2031-02-04', ['9990001']));
    }
}
