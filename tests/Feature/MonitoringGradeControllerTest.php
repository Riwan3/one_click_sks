<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MonitoringGradeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create(['username' => 'tmg'.random_int(10000, 99999), 'role' => 'admin']);
    }

    public function test_grade_tab_lists_filters_and_paginates(): void
    {
        DB::table('odp_mg_grade_v4')->insert([
            ['nrp' => 'MG1', 'nama' => 'Budi', 'posisi' => 'Operator Dozer', 'grade_now' => 'OP3', 'suite' => 'YES', 'created_at' => now(), 'updated_at' => now()],
            ['nrp' => 'MG2', 'nama' => 'Siti', 'posisi' => 'Operator Truck', 'grade_now' => 'OP2', 'suite' => 'NO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->admin())
            ->get('/one-academy/monitoring-grade?tab=grade')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('OneAcademy/MonitoringGrade/Index')
                ->where('tab', 'grade')
                ->where('totalRows', 2)
                ->has('rows', 2)
                ->has('columns', 29)
            );

        $this->actingAs($this->admin())
            ->get('/one-academy/monitoring-grade?tab=grade&filter_posisi=Operator+Dozer')
            ->assertInertia(fn (Assert $page) => $page->where('totalRows', 1));
    }

    public function test_monitoring_tab_aggregates_by_position(): void
    {
        DB::table('odp_mg_grade_v4')->insert([
            ['nrp' => 'MG1', 'nama' => 'Budi', 'posisi' => 'Operator Dozer', 'grade_now' => 'OP3', 'suite' => 'YES', 'created_at' => now(), 'updated_at' => now()],
            ['nrp' => 'MG2', 'nama' => 'Siti', 'posisi' => 'Operator Dozer', 'grade_now' => 'OP2', 'suite' => 'NO', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->admin())
            ->get('/one-academy/monitoring-grade?tab=monitoring')
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.0.type_unit', 'Operator Dozer')
                ->where('rows.0.actual_operator', 2)
                ->where('rows.0.grade_sesuai', 1)
                ->where('rows.0.grade_belum_sesuai', 1)
            );
    }

    public function test_admin_can_add_grade_and_versatility_records(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/one-academy/monitoring-grade/store-grade', ['nrp' => 'MG9', 'nama' => 'Joko', 'posisi' => 'Operator Dozer'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $this->assertDatabaseHas('odp_mg_grade_v4', ['nrp' => 'MG9', 'nama' => 'Joko']);

        $this->actingAs($admin)
            ->post('/one-academy/monitoring-grade/store-versatility', [
                'nrp' => 'MG9', 'nama' => 'Joko', 'jabatan' => 'Operator', 'versality' => '3',
                'grp1_wt_ft_lt' => 'V', 'grp7_d475' => 'V',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $this->assertDatabaseHas('odp_mg_versality_v5', ['nrp' => 'MG9', 'grp1_wt_ft_lt' => 'V', 'grp7_d475' => 'V']);
    }

    public function test_sync_grade_replaces_table_from_csv(): void
    {
        $csv = "No,NRP,Nama,Posisi\n1,MG10,Test Sync,Operator Dozer\n";
        Http::fake(['docs.google.com/*' => Http::response($csv, 200)]);

        DB::table('odp_mg_grade_v4')->insert(['nrp' => 'OLD', 'nama' => 'Old Row', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->admin())
            ->post('/one-academy/monitoring-grade/sync-grade', ['sheets_url' => 'https://docs.google.com/spreadsheets/d/abc123/edit#gid=0'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing('odp_mg_grade_v4', ['nrp' => 'OLD']);
        $this->assertDatabaseHas('odp_mg_grade_v4', ['nrp' => 'MG10', 'nama' => 'Test Sync']);
        $this->assertSame('https://docs.google.com/spreadsheets/d/abc123/edit#gid=0', DB::table('odp_mg_settings')->where('setting_key', 'grade_url')->value('setting_value'));
    }

    public function test_sync_fails_gracefully_when_sheet_not_public(): void
    {
        Http::fake(['docs.google.com/*' => Http::response('<html>Sign in - Google Accounts</html>', 200)]);

        $this->actingAs($this->admin())
            ->post('/one-academy/monitoring-grade/sync-grade', ['sheets_url' => 'https://docs.google.com/spreadsheets/d/abc123/edit#gid=0'])
            ->assertSessionHasErrors('sheets_url');
    }
}
