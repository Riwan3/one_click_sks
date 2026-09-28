<?php

namespace Tests\Feature;

use App\Enums\EmployeeStatus;
use App\Models\BestEmployeeCampaign;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BestEmployeeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->create([
            'username' => 'tbe'.random_int(10000, 99999),
            'role' => 'admin',
        ]);
    }

    private function participantUser(): User
    {
        $employee = Employee::create([
            'nrp' => 'BE'.random_int(10000, 99999),
            'name' => 'Peserta Test',
            'jabatan_ss6' => 'Operator',
            'unit' => 'Produksi',
            'lokasi_kerja' => 'Site A',
            'status_karyawan' => EmployeeStatus::Fit->value,
        ]);

        return User::factory()->create([
            'username' => 'tbeu'.random_int(10000, 99999),
            'role' => 'operator',
            'employee_id' => $employee->id,
        ]);
    }

    public function test_admin_can_create_and_configure_a_campaign(): void
    {
        $admin = $this->admin();
        $participant = $this->participantUser();

        $this->actingAs($admin)
            ->post('/one-academy/best-employee', ['judul' => 'Best Employee Test', 'deskripsi' => 'desc'])
            ->assertRedirect();

        $campaign = BestEmployeeCampaign::where('judul', 'Best Employee Test')->firstOrFail();
        $this->assertSame('draft', $campaign->status);
        $this->assertSame($admin->id, $campaign->created_by);

        $this->actingAs($admin)
            ->put("/one-academy/best-employee/{$campaign->id}", [
                'judul' => $campaign->judul,
                'aturan' => 'Kerjakan dengan jujur',
                'publish_start' => now()->addDay()->format('Y-m-d\TH:i'),
                'publish_end' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'default_question_duration_seconds' => 60,
                'attempt_limit' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->actingAs($admin)
            ->post("/one-academy/best-employee/{$campaign->id}/questions", [
                'pertanyaan' => 'Ibukota Indonesia?',
                'tipe_soal' => 'multiple_choice',
                'bobot' => 2,
                'is_required' => true,
                'use_default_duration' => true,
                'options' => [
                    ['option_text' => 'Jakarta', 'is_correct' => true],
                    ['option_text' => 'Bandung', 'is_correct' => false],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(1, $campaign->questions()->count());

        $this->actingAs($admin)
            ->post("/one-academy/best-employee/{$campaign->id}/participants/assign", ['user_ids' => [$participant->id]])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(1, $campaign->participants()->assigned()->count());

        $this->actingAs($admin)
            ->post("/one-academy/best-employee/{$campaign->id}/publish")
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $campaign->refresh();
        $this->assertSame('scheduled', $campaign->status);

        $this->actingAs($admin)
            ->post("/one-academy/best-employee/{$campaign->id}/cancel", ['reason' => 'Uji coba selesai'])
            ->assertRedirect();

        $campaign->refresh();
        $this->assertSame('cancelled', $campaign->status);
    }

    public function test_locked_critical_settings_require_force_and_reason(): void
    {
        $admin = $this->admin();

        $campaign = BestEmployeeCampaign::create([
            'judul' => 'Locked Campaign',
            'status' => 'active',
            'publish_start' => now()->subHour(),
            'publish_end' => now()->addHour(),
            'default_question_duration_seconds' => 60,
            'attempt_limit' => 1,
        ]);

        $this->actingAs($admin)
            ->put("/one-academy/best-employee/{$campaign->id}", [
                'judul' => $campaign->judul,
                'publish_start' => now()->addDay()->format('Y-m-d\TH:i'),
                'publish_end' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'default_question_duration_seconds' => 60,
                'attempt_limit' => 1,
            ])
            ->assertSessionHasErrors('reason');

        $this->actingAs($admin)
            ->put("/one-academy/best-employee/{$campaign->id}", [
                'judul' => $campaign->judul,
                'publish_start' => now()->addDay()->format('Y-m-d\TH:i'),
                'publish_end' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'default_question_duration_seconds' => 60,
                'attempt_limit' => 1,
                'force' => true,
                'reason' => 'Jadwal mundur atas permintaan pimpinan',
            ])
            ->assertSessionHasNoErrors();
    }
}
