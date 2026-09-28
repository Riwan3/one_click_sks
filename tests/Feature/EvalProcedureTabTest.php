<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EvalProcedureTabTest extends TestCase
{
    use DatabaseTransactions;

    private const BASE = '/one-procedure/evaluasi/procedure';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckSessionSecurity::class);
    }

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role, 'username' => 'tprc'.$role.random_int(1000, 9999)]);
    }

    public function test_manager_can_create_procedure_with_file(): void
    {
        $response = $this->actingAs($this->userWithRole('admin'))->post(self::BASE.'/store', [
            'title' => 'SOP Test Tab Procedure',
            'category' => 'K3',
            'risk_level' => 'high',
            'file' => UploadedFile::fake()->create('sop.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect()->assertSessionHas('success', 'Prosedur berhasil ditambahkan');
        $procedure = Procedure::where('title', 'SOP Test Tab Procedure')->firstOrFail();
        $this->assertSame('pdf', $procedure->file_type);
        $this->assertNull($procedure->effective_date);

        unlink(public_path(ltrim($procedure->file_path, '/')));
    }

    public function test_procedure_title_is_required(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(self::BASE.'/store', ['title' => ''])
            ->assertSessionHasErrors(['title' => 'Judul prosedur wajib diisi']);
    }

    public function test_operator_cannot_create_or_delete_procedure(): void
    {
        $operator = $this->userWithRole('operator');
        $procedure = Procedure::create(['title' => 'SOP Test Operator', 'risk_level' => 'low']);

        $this->actingAs($operator)->post(self::BASE.'/store', ['title' => 'X'])->assertForbidden();
        $this->actingAs($operator)->delete(self::BASE."/delete/{$procedure->id}")->assertForbidden();
    }

    public function test_procedure_tab_filters_and_counts(): void
    {
        Procedure::create(['title' => 'SOP Filter Unik Zeta', 'risk_level' => 'critical', 'category' => 'K3']);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/one-procedure/evaluasi?tab=procedure&search=Filter+Unik+Zeta&risk=critical')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('procedures', 1)
                ->where('procedures.0.title', 'SOP Filter Unik Zeta')
                ->where('procedures.0.total_assigned', 0));
    }
}
