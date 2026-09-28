<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBestEmployeeCampaignRequest;
use App\Http\Requests\UpdateBestEmployeeCampaignRequest;
use App\Models\BestEmployeeCampaign;
use App\Services\BestEmployeeCampaignService;
use App\Services\BestEmployeeResultService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class BestEmployeeController extends Controller
{
    public function __construct(
        private BestEmployeeCampaignService $campaigns,
        private BestEmployeeResultService $results,
    ) {}

    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $can = [
            'manage' => $user->canManageBestEmployee(),
            'viewResults' => $user->canViewBestEmployeeResults(),
        ];

        $campaignId = (int) $request->integer('id');
        $tab = in_array($request->string('tab')->toString(), ['pengaturan', 'soal', 'peserta', 'monitoring', 'hasil'], true)
            ? $request->string('tab')->toString()
            : 'pengaturan';

        $shared = [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'id' => $campaignId,
            'tab' => $tab,
            'can' => $can,
        ];

        if ($campaignId <= 0) {
            $campaigns = BestEmployeeCampaign::query()
                ->withCount([
                    'questions',
                    'participants as participants_count' => fn ($q) => $q->assigned(),
                ])
                ->latest('id')
                ->get()
                ->map(fn (BestEmployeeCampaign $c) => [
                    ...$c->only(['id', 'judul', 'deskripsi', 'publish_start', 'publish_end', 'status']),
                    'effective_status' => $c->effectiveStatus()->value,
                    'total_questions' => $c->questions_count,
                    'total_participants' => $c->participants_count,
                ]);

            return Inertia::render('OneAcademy/BestEmployee/Index', [...$shared, 'campaigns' => $campaigns]);
        }

        $campaign = BestEmployeeCampaign::findOrFail($campaignId);
        $campaignData = [
            ...$campaign->toArray(),
            'effective_status' => $campaign->effectiveStatus()->value,
            'questions_locked' => $campaign->questionsLocked(),
            'participants_locked' => $campaign->participantsLocked(),
            'settings_critical_locked' => $campaign->settingsCriticalLocked(),
            'stats' => $this->campaigns->stats($campaign),
        ];

        $data = [...$shared, 'campaign' => $campaignData];

        match ($tab) {
            'soal' => $data['questions'] = $campaign->questions()->with('options')->get(),
            'peserta' => $data['participants'] = $campaign->participants()->assigned()->latest('id')->get(),
            'monitoring' => $data = [
                ...$data,
                'filterOptions' => $this->results->participantFilterOptions($campaign),
                'monitoringSummary' => Inertia::optional(fn () => $this->results->monitoringSummary($campaign)),
                'monitoringList' => Inertia::optional(fn () => $this->results->monitoringList(
                    $campaign,
                    $request->only(['q', 'status', 'jabatan', 'unit', 'min_score', 'max_score']),
                    (int) $request->integer('page', 1)
                )),
            ],
            'hasil' => $data = [
                ...$data,
                'filterOptions' => $this->results->participantFilterOptions($campaign),
                'rankingRows' => Inertia::optional(fn () => $this->results->ranking($campaign, $request->only(['q', 'jabatan']))),
            ],
            default => null,
        };

        if ($tab === 'pengaturan' && $campaign->status === 'draft') {
            $data['publishReadiness'] = $this->campaigns->publishReadiness($campaign);
        }

        return Inertia::render('OneAcademy/BestEmployee/Index', $data);
    }

    public function store(StoreBestEmployeeCampaignRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->create($request->validated(), Auth::id());

        return redirect()->route('one-academy.best-employee.index', ['id' => $campaign->id, 'tab' => 'pengaturan'])
            ->with('success', 'Kegiatan Best Employee berhasil dibuat.');
    }

    public function update(UpdateBestEmployeeCampaignRequest $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        $locked = $campaign->settingsCriticalLocked();
        $validated = $request->validated();
        $touchesCritical = $this->touchesCriticalFields($validated, $campaign);

        if ($locked && $touchesCritical && ! ($request->boolean('force') && $request->filled('reason'))) {
            return back()->withErrors(['reason' => 'Kegiatan sudah dipublish - pengaturan jadwal & perilaku terkunci. Centang override dan isi alasan untuk mengubahnya.']);
        }

        $this->campaigns->update($campaign, $validated, Auth::id(), true, $request->boolean('force') ? $request->string('reason')->toString() : null);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    /** Bandingkan nilai field kritis yang dikirim vs nilai tersimpan - untuk menentukan apakah override perlu ditegakkan. */
    private function touchesCriticalFields(array $validated, BestEmployeeCampaign $campaign): bool
    {
        return collect(BestEmployeeCampaign::CRITICAL_FIELDS)->contains(function ($field) use ($validated, $campaign) {
            if (! array_key_exists($field, $validated)) {
                return false;
            }
            $incoming = $validated[$field];
            $current = $campaign->getAttribute($field);

            if ($current instanceof \Illuminate\Support\Carbon) {
                return ! $incoming || ! $current->eq($incoming);
            }
            if (is_bool($current)) {
                return (bool) $incoming !== $current;
            }

            return (string) $incoming !== (string) $current;
        });
    }

    public function publish(BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);
        abort_unless($campaign->status === 'draft', 422, 'Hanya kegiatan berstatus draft yang bisa dipublish.');

        $readiness = $this->campaigns->publishReadiness($campaign);
        if (! $readiness['ready']) {
            return back()->withErrors(['publish' => 'Kegiatan belum siap dipublish. Lengkapi checklist kesiapan terlebih dahulu.']);
        }

        $this->campaigns->publish($campaign, Auth::id());

        return back()->with('success', 'Kegiatan berhasil dipublish.');
    }

    public function cancel(Request $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);
        abort_if($campaign->status === 'finished', 422, 'Kegiatan yang sudah selesai tidak dapat dibatalkan.');

        $this->campaigns->cancel($campaign, Auth::id(), $request->string('reason')->toString() ?: null);

        return back()->with('success', 'Kegiatan dibatalkan.');
    }

    public function destroy(Request $request, BestEmployeeCampaign $campaign): RedirectResponse
    {
        abort_unless(Auth::user()->canManageBestEmployee(), 403);
        abort_if($campaign->effectiveStatus()->value === 'active', 423, 'Kegiatan sedang aktif/berjalan tidak dapat dihapus. Batalkan dulu jika diperlukan.');

        $request->validate([
            'confirm_username' => ['required', 'string'],
            'confirm_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        if ($request->string('confirm_username')->toString() !== $user->username
            || ! Hash::check($request->string('confirm_password')->toString(), $user->password)) {
            return back()->withErrors(['confirm_password' => 'Username atau password tidak sesuai dengan akun yang sedang login.']);
        }

        $this->campaigns->softDelete($campaign, $user->id);

        return redirect()->route('one-academy.best-employee.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
