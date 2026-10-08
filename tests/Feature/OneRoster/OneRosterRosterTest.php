<?php

namespace Tests\Feature\OneRoster;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Employee;
use App\Models\User;
use App\Repositories\RosterV2Repository;
use App\Services\RosterV2Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OneRosterRosterTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-roster/roster';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'rv2admin', 'name' => 'RV2 Admin', 'password' => Hash::make('rahasia123')]);
    }

    private function createRoster(User $admin, array $overrides = []): int
    {
        $response = $this->actingAs($admin)->post(self::BASE, [
            'roster_group' => 'TEST RV2', 'year' => 2031, 'notes' => 'uji', ...$overrides,
        ]);
        $id = (int) app(RosterV2Repository::class)->findRosterByPeriod($overrides['roster_group'] ?? 'TEST RV2', '2031-01-01', '2031-12-31')['id'];
        $response->assertRedirect(self::BASE.'/'.$id);

        return $id;
    }

    private function employee(string $nrp = 'T900001'): Employee
    {
        return Employee::create(['nrp' => $nrp, 'name' => 'Karyawan Uji '.$nrp, 'jabatan_ss6' => 'OPERATOR TEST', 'unit' => 'PIT A']);
    }

    public function test_admin_can_open_roster_index(): void
    {
        $this->actingAs($this->admin())->get(self::BASE)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Roster/Index')
                ->where('canManage', true)
                ->has('summary.total')
                ->has('rosters'));
    }

    public function test_regular_operator_cannot_open_roster_pages(): void
    {
        $operator = User::factory()->create(['role' => 'operator', 'username' => 'rv2op']);

        $this->actingAs($operator)->get(self::BASE)->assertForbidden();
        $this->actingAs($operator)->getJson(self::BASE.'/employees/search?q=ab')->assertForbidden();
    }

    public function test_view_only_user_cannot_create_roster(): void
    {
        $pimpinan = User::factory()->create(['role' => 'pimpinan', 'username' => 'rv2pimp']);

        $this->actingAs($pimpinan)->get(self::BASE)->assertOk();
        $this->actingAs($pimpinan)->post(self::BASE, ['roster_group' => 'X', 'year' => 2031])->assertForbidden();
    }

    public function test_create_roster_validates_group_and_rejects_duplicate_period(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(self::BASE, ['roster_group' => '', 'year' => 2031])
            ->assertSessionHasErrors(['form' => 'Roster Group wajib diisi.']);

        $this->createRoster($admin);
        $this->actingAs($admin)->post(self::BASE, ['roster_group' => 'TEST RV2', 'year' => 2031])
            ->assertSessionHasErrors(['form' => 'Roster untuk grup & periode ini sudah ada.']);
    }

    public function test_roster_lifecycle_section_member_cell_publish_archive(): void
    {
        $admin = $this->admin();
        $this->employee();
        $id = $this->createRoster($admin);
        $url = self::BASE.'/'.$id;

        $this->actingAs($admin)->post("$url/sections", ['name' => 'admin produksi'])->assertSessionHas('success');
        $section = app(RosterV2Repository::class)->findSectionByName($id, 'ADMIN PRODUKSI');
        $this->assertNotNull($section);

        $this->actingAs($admin)->post("$url/sections/{$section['id']}/members", ['nrp' => 'T900001', 'kode_off' => 'A1', 'work_pattern' => 'ROTASI'])
            ->assertSessionHas('success');
        $this->assertGreaterThan(300, app(RosterV2Repository::class)->countDetails($id));   // putaran Kode Off mengisi 1 tahun

        $this->actingAs($admin)->postJson("$url/cells", ['nrp' => 'T900001', 'work_date' => '2031-03-05', 'shift_code' => 'CT'])
            ->assertOk()->assertJson(['success' => true, 'data' => ['code' => 'CT']]);

        $this->actingAs($admin)->get("$url?tab=matrix&month=2031-03")
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('OneRoster/Roster/Show')
                ->where('canEdit', true)
                ->where('matrix.month', '2031-03')
                ->where('matrix.sections.0.members.0.cells.2031-03-05', 'CT')
                ->where('changelog.ready', true)
                ->has('changelog.rows'));

        $this->actingAs($admin)->post("$url/status", ['status' => 'published'])->assertSessionHas('success');
        $this->assertSame('published', app(RosterV2Repository::class)->findRoster($id)['status']);

        $this->actingAs($admin)->post("$url/status", ['status' => 'archived', 'auth_nrp' => 'rv2admin', 'auth_password' => 'salah'])
            ->assertSessionHasErrors(['form' => 'NRP atau password salah.']);
        $this->actingAs($admin)->post("$url/status", ['status' => 'archived', 'auth_nrp' => 'rv2admin', 'auth_password' => 'rahasia123'])
            ->assertSessionHas('success');

        $this->actingAs($admin)->postJson("$url/cells", ['nrp' => 'T900001', 'work_date' => '2031-03-06', 'shift_code' => 'D'])
            ->assertStatus(409)->assertJson(['success' => false, 'message' => 'Roster sudah diarsipkan, tidak dapat diubah.']);
    }

    public function test_detail_list_add_and_allocation(): void
    {
        $admin = $this->admin();
        $this->employee();
        $id = $this->createRoster($admin);
        $url = self::BASE.'/'.$id;

        $this->actingAs($admin)->post("$url/details", ['nrp' => 'T900001', 'work_date' => '2031-02-10', 'shift_code' => 'D', 'unit' => 'PIT A', 'section' => 'S1'])
            ->assertSessionHas('success');
        $this->actingAs($admin)->post("$url/details", ['nrp' => 'T900001', 'work_date' => '2031-02-10', 'shift_code' => 'N'])
            ->assertSessionHasErrors('form');

        $this->actingAs($admin)->get("$url?tab=list")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('list.total', 1)
                ->where('list.details.0.allocations.0.unit', 'PIT A'));
    }

    public function test_employee_search_and_upload_tab(): void
    {
        $admin = $this->admin();
        $this->employee();
        $id = $this->createRoster($admin);

        $this->actingAs($admin)->getJson(self::BASE.'/employees/search?q=Karyawan Uji')->assertOk()->assertJsonPath('data.0.nrp', 'T900001');
        $this->actingAs($admin)->getJson(self::BASE.'/employees/search?nrp=TIDAKADA')->assertStatus(404);
        $this->actingAs($admin)->get(self::BASE."/{$id}?tab=upload&month=2031-02")
            ->assertInertia(fn (AssertableInertia $page) => $page->component('OneRoster/Roster/Show')->where('upload.month', '2031-02')->has('upload.dates'));
    }

    public function test_sheet_import_preview_rejects_non_google_url(): void
    {
        $admin = $this->admin();
        $id = $this->createRoster($admin);

        $this->actingAs($admin)->postJson(self::BASE.'/sheet-import/preview', ['url' => 'https://example.com/x', 'roster_id' => $id])
            ->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_import_template_download_and_existing_published_roster_matrix_loads(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(self::BASE.'/import-template')->assertOk();

        $existing = app(RosterV2Service::class);
        $this->actingAs($admin);
        $rosters = $existing->listRosters(['status' => 'published']);
        if (! $rosters) {
            $this->markTestSkipped('Belum ada roster published pada DB.');
        }
        $started = microtime(true);
        $this->get(self::BASE.'/'.$rosters[0]['id'])
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('matrix.month', fn ($m) => is_string($m))->has('matrix.sections'));
        $this->assertLessThan(10, microtime(true) - $started, 'Matriks roster nyata terlalu lambat.');
    }

    public function test_duplicate_move_rotation_and_fill_range(): void
    {
        $admin = $this->admin();
        $this->employee("T900001");
        $this->employee("T900002");
        $id = $this->createRoster($admin);
        $url = self::BASE."/".$id;
        $repo = app(RosterV2Repository::class);

        $this->actingAs($admin)->post("$url/sections", ["name" => "SEC A"]);
        $this->actingAs($admin)->post("$url/sections", ["name" => "SEC B"]);
        $a = $repo->findSectionByName($id, "SEC A")["id"];
        $b = $repo->findSectionByName($id, "SEC B")["id"];
        $this->actingAs($admin)->post("$url/sections/$a/members", ["nrp" => "T900001", "kode_off" => "A1"]);
        $this->actingAs($admin)->post("$url/sections/$a/members", ["nrp" => "T900002"]);
        $member = $repo->findMemberByNrp($id, "T900002");

        $this->actingAs($admin)->post("$url/members/{$member["id"]}/move", ["section_id" => $b, "position" => 1])->assertSessionHas("success");
        $this->assertSame($b, (int) $repo->findMemberByNrp($id, "T900002")["section_id"]);

        $this->actingAs($admin)->post("$url/members/{$member["id"]}/rotation", ["kode_off" => "B3", "date_from" => "2031-01-01", "date_to" => "2031-01-31", "apply" => true, "work_pattern" => "FULL_SIANG"])
            ->assertSessionHas("success");
        $this->assertSame(31, count($repo->getCellCodesInRange($id, "2031-01-01", "2031-01-31")["T900002"]));

        $this->actingAs($admin)->post("$url/cells/fill", ["nrp" => "T900002", "date_from" => "2031-01-01", "date_to" => "2031-01-10", "shift_code" => ""])
            ->assertSessionHas("success");
        $this->assertSame(21, count($repo->getCellCodesInRange($id, "2031-01-01", "2031-01-31")["T900002"]));

        $this->actingAs($admin)->post("$url/duplicate", ["roster_group" => "TEST RV2 COPY", "year" => 2032, "create_moncu" => false])->assertRedirect();
        $copy = $repo->findRosterByPeriod("TEST RV2 COPY", "2032-01-01", "2032-12-31");
        $this->assertNotNull($copy);
        $this->assertCount(2, $repo->listSections($copy["id"]));
        $this->assertCount(2, $repo->listMembers($copy["id"]));

        $this->actingAs($admin)->get("$url/upload/download?month=2031-01")->assertOk()->assertHeader("content-disposition");
    }

    public function test_excel_import_preview_and_commit(): void
    {
        $admin = $this->admin();
        $this->employee("T900001");
        $id = $this->createRoster($admin);
        $path = \App\Support\RosterV2Xlsx::buildTable("Roster", ["NRP", "Nama", "Jabatan", "Tanggal", "Shift", "Unit", "Section"], [
            ["T900001", "Karyawan Uji T900001", "OPERATOR", "2031-04-01", "D", "PIT A", "S1"],
            ["T900001", "", "", "2031-04-02", "ZZ", "", ""],
        ], [3]);
        $file = new \Illuminate\Http\UploadedFile($path, "import.xlsx", null, null, true);

        $preview = $this->actingAs($admin)->post(self::BASE."/$id/import/preview", ["file" => $file])->assertOk()
            ->assertJsonPath("data.summary.valid", 1)->assertJsonPath("data.summary.error", 1);

        $this->actingAs($admin)->postJson(self::BASE."/$id/import/commit", ["token" => $preview->json("data.token")])
            ->assertOk()->assertJsonPath("data.imported", 1);
        $this->assertSame(1, app(RosterV2Repository::class)->countDetails($id));
    }
}
