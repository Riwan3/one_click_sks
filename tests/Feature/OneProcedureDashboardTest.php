<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionSecurity;
use App\Models\ProsedurKerja;
use App\Models\ProsedurType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class OneProcedureDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_summarizes_documents_from_prosedur_menu(): void
    {
        $this->withoutMiddleware(CheckSessionSecurity::class);
        $type = ProsedurType::create(['slug' => 'dashtest', 'name' => 'DASH TEST']);
        ProsedurKerja::create(['jenis' => $type->slug, 'kode_dokumen' => 'DT-001', 'judul' => 'Dokumen Dashboard Uji', 'status' => 'aktif']);
        $user = User::factory()->create(['role' => 'operator', 'username' => 'tdash'.random_int(1000, 9999)]);

        $this->actingAs($user)
            ->get('/one-procedure/dashboard?search=Dashboard+Uji')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('documents.types', fn ($types) => collect($types)->contains(fn ($t) => $t['name'] === 'DASH TEST' && $t['total'] === 1))
                ->has('recentDocuments', 1)
                ->where('recentDocuments.0.jenis_label', 'DASH TEST')
                ->has('evaluation.certificates'));
    }
}
