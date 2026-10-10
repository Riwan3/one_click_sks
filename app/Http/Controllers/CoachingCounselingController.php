<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachingCounselingRequest;
use App\Http\Requests\UpdateCoachingCounselingRequest;
use App\Http\Resources\CoachingCounselingResource;
use App\Http\Resources\EmployeeResource;
use App\Models\CoachingCounseling;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\CoachingCounselingService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;

class CoachingCounselingController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private CoachingCounselingService $coachingCounselingService) {}

    /**
     * API mobile: daftar coaching & counseling (hanya baca) untuk GL/admin. Aturan cakupan data
     * sama dengan halaman web (GL hanya melihat milik/anak asuhnya). Tanda tangan (base64)
     * tidak dikirim; hanya penanda ada/tidaknya.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->isGroupLeader() || $user->isAdmin()), 403, 'Menu ini hanya untuk Group Leader.');

        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $gl = $user->isGroupLeader() && ! $user->isAdmin() ? $user->getEmployeeRecord() : null;

        $items = $this->coachingCounselingService->paginate(
            $search,
            $dateFrom ?: null,
            $dateTo ?: null,
            $page,
            self::PER_PAGE,
            $gl,
            $user
        );

        return response()->json([
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'items' => collect(CoachingCounselingResource::collection($items->items())->resolve())
                ->map(function (array $row) {
                    $row['hasTtdAnakAsuh'] = ! empty($row['ttdAnakAsuh']);
                    $row['hasTtdBapaAsuh'] = ! empty($row['ttdBapaAsuh']);
                    unset($row['ttdAnakAsuh'], $row['ttdBapaAsuh']);

                    return $row;
                })
                ->values(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $user = $request->user();
        $gl = null;
        if ($user && $user->isGroupLeader() && ! $user->isAdmin()) {
            $gl = $user->getEmployeeRecord();
        }

        $items = $this->coachingCounselingService->paginate(
            $search,
            $dateFrom ?: null,
            $dateTo ?: null,
            $page,
            self::PER_PAGE,
            $gl,
            $user
        );

        return Inertia::render('OnePower/CoachingCounseling/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => CoachingCounselingResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
            'documentSettings' => $this->getDocumentSettings(),
            'gl' => $gl ? (new EmployeeResource($gl))->resolve() : null,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $defaultCoach = null;
        $user = $request->user();
        if ($user) {
            $user->loadMissing('employee');
            $emp = $user->employee;

            if (! $emp && $user->employee_id) {
                $emp = Employee::find($user->employee_id);
            }

            if (! $emp && $user->username) {
                $emp = Employee::where('nrp', $user->username)->first();
            }

            if (! $emp && $user->name) {
                $emp = Employee::where('name', $user->name)->first();
            }

            if (! $emp) {
                $emp = Employee::firstOrCreate(
                    ['nrp' => $user->username ?: 'USR'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT)],
                    [
                        'name' => $user->name,
                        'jabatan_ss6' => $user->jabatan ?: 'GL / COACH',
                        'unit' => $user->unit ?: '-',
                        'email' => $user->email,
                    ]
                );
                if (! $user->employee_id) {
                    $user->update(['employee_id' => $emp->id]);
                }
            }

            if ($emp) {
                $defaultCoach = [
                    'id' => $emp->id,
                    'nrp' => $emp->nrp,
                    'name' => $emp->name,
                    'jabatan_ss6' => $emp->jabatan_ss6,
                    'unit' => $emp->unit,
                ];
            }
        }

        return Inertia::render('OnePower/CoachingCounseling/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'defaultCoach' => $defaultCoach,
        ]);
    }

    public function store(StoreCoachingCounselingRequest $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', 'Coaching & counseling berhasil dibuat.');
    }

    public function edit(Request $request, CoachingCounseling $coachingCounseling): Response
    {
        abort_if($request->user()?->isManagement(), 403);

        $coachingCounseling->load(['employee', 'coach']);

        return Inertia::render('OnePower/CoachingCounseling/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => new CoachingCounselingResource($coachingCounseling),
        ]);
    }

    public function update(UpdateCoachingCounselingRequest $request, CoachingCounseling $coachingCounseling): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->update($coachingCounseling, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', 'Coaching & counseling berhasil diperbarui.');
    }

    public function destroy(Request $request, CoachingCounseling $coachingCounseling): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $this->coachingCounselingService->delete($coachingCounseling);

        return redirect()->back()->with('success', 'Coaching & counseling berhasil dihapus.');
    }

    public function printBlankForm(): Response
    {
        return Inertia::render('OnePower/CoachingCounseling/PrintForm', [
            'item' => null,
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function printForm(CoachingCounseling $coachingCounseling): Response
    {
        $coachingCounseling->load(['employee', 'coach']);

        return Inertia::render('OnePower/CoachingCounseling/PrintForm', [
            'item' => (new CoachingCounselingResource($coachingCounseling))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function printResult(CoachingCounseling $coachingCounseling): Response
    {
        $coachingCounseling->load(['employee', 'coach']);

        return Inertia::render('OnePower/CoachingCounseling/PrintResult', [
            'item' => (new CoachingCounselingResource($coachingCounseling))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function show(Request $request, CoachingCounseling $coachingCounseling): Response
    {
        if ($request->query('type') === 'form') {
            return $this->printForm($coachingCounseling);
        }

        return $this->printResult($coachingCounseling);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $validated = $request->validate([
            'siteCode' => ['required', 'string', 'max:50'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'noDokumen' => ['required', 'string', 'max:100'],
            'revisi' => ['nullable', 'string', 'max:50'],
            'tglEfektif' => ['nullable', 'date'],
        ]);

        $siteCode = strtoupper(trim($validated['siteCode']));
        $lokasi = trim($validated['lokasi'] ?? 'Tabalong');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '1');
        $tglEfektif = $validated['tglEfektif'] ?? '2022-06-07';

        Setting::setValue('coaching_counseling_site_code', $siteCode);
        Setting::setValue('coaching_counseling_lokasi', $lokasi);
        Setting::setValue('coaching_counseling_no_dokumen', $noDokumen);
        Setting::setValue('coaching_counseling_revisi', $revisi);
        Setting::setValue('coaching_counseling_tgl_efektif', $tglEfektif);

        CoachingCounseling::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat coaching counseling berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('coaching_counseling_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('coaching_counseling_lokasi', 'Tabalong') ?: 'Tabalong',
            'noDokumen' => Setting::getValue('coaching_counseling_no_dokumen', 'PPA-ADRO-F-HCGA-17') ?: 'PPA-ADRO-F-HCGA-17',
            'revisi' => Setting::getValue('coaching_counseling_revisi', '1') ?: '1',
            'tglEfektif' => Setting::getValue('coaching_counseling_tgl_efektif', '2022-06-07') ?: '2022-06-07',
        ];
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $employees = Employee::query()
            ->search($keyword)
            ->limit(10)
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit']);

        return response()->json($employees);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $count = $this->coachingCounselingService->importFromExcel($request->file('file'), $request->user()?->id);

        return redirect('/one-power/pembinaan/coaching-counseling')->with('success', "Berhasil mengimport {$count} coaching & counseling.");
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = ['nrp', 'nama_karyawan', 'jabatan', 'tanggal', 'lokasi', 'waktu_pertemuan', 'materi_pribadi', 'materi_keluarga', 'materi_lingkungan', 'materi_pekerjaan', 'fatigue_level', 'deskripsi', 'rekomendasi', 'coach'];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $h = fopen('php://output', 'w');
            fputcsv($h, $headers);
            fputcsv($h, ['10001', 'Budi Santoso', 'Operator', '2026-08-26', 'CSA Inggris', '14:00', '1', '0', '1', '0', '2', 'Contoh deskripsi coaching...', 'Contoh rekomendasi...', '10002']);
            fclose($h);
        }, 'template_coaching.csv', ['Content-Type' => 'text/csv']);
    }
}
