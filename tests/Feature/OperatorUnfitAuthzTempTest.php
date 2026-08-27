<?php

namespace Tests\Feature;

use App\Models\OperatorUnfit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OperatorUnfitAuthzTempTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUnfit(): OperatorUnfit
    {
        return OperatorUnfit::create([
            'nrp' => 'X1',
            'nama' => 'Test Operator',
            'perusahaan' => 'PT. PUTRA PERKASA ABADI',
            'departemen' => 'Produksi',
            'tgl_mulai_unfit' => now(),
        ]);
    }

    public function test_authorized_roles_can_access_every_action(): void
    {
        $suffix = (string) random_int(1000, 9999);
        $base = '/one-power/deviasi-operator/unfit';

        foreach (['admin', 'pimpinan', 'group-leader', 'medic'] as $role) {
            $user = User::factory()->create(['role' => $role, 'username' => 'au'.$role.$suffix]);
            $unfit = $this->makeUnfit();

            $this->actingAs($user)->get($base)->assertOk();
            $this->actingAs($user)->get("$base/create")->assertOk();
            $this->actingAs($user)->get("$base/search-employee?keyword=x")->assertOk();

            $this->actingAs($user)->post($base, [
                'nrp' => 'X2',
                'nama' => 'New Operator',
                'tgl_mulai_unfit' => now()->toDateString(),
            ])->assertRedirect();

            $this->actingAs($user)->get("$base/{$unfit->id}/edit")->assertOk();

            $this->actingAs($user)->put("$base/{$unfit->id}", [
                'nrp' => $unfit->nrp,
                'nama' => $unfit->nama,
                'tgl_mulai_unfit' => now()->toDateString(),
            ])->assertRedirect();

            $this->actingAs($user)->post("$base/{$unfit->id}/end")->assertRedirect();

            $this->actingAs($user)->delete("$base/{$unfit->id}")->assertRedirect();
        }
    }

    public function test_unauthorized_role_gets_403_on_every_action(): void
    {
        $suffix = (string) random_int(1000, 9999);
        $base = '/one-power/deviasi-operator/unfit';

        $operator = User::factory()->create(['role' => 'operator', 'username' => 'uo'.$suffix]);
        $unfit = $this->makeUnfit();

        $this->actingAs($operator)->get($base)->assertForbidden();
        $this->actingAs($operator)->get("$base/create")->assertForbidden();
        $this->actingAs($operator)->get("$base/search-employee?keyword=x")->assertForbidden();

        $this->actingAs($operator)->post($base, [
            'nrp' => 'X3',
            'nama' => 'Should Not Be Created',
            'tgl_mulai_unfit' => now()->toDateString(),
        ])->assertForbidden();

        $this->actingAs($operator)->get("$base/{$unfit->id}/edit")->assertForbidden();

        $this->actingAs($operator)->put("$base/{$unfit->id}", [
            'nrp' => $unfit->nrp,
            'nama' => $unfit->nama,
            'tgl_mulai_unfit' => now()->toDateString(),
        ])->assertForbidden();

        $this->actingAs($operator)->post("$base/{$unfit->id}/end")->assertForbidden();

        $this->actingAs($operator)->delete("$base/{$unfit->id}")->assertForbidden();

        $this->assertDatabaseMissing('operator_unfits', ['nrp' => 'X3']);
    }
}
