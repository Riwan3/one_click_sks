<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFatigueInterviewRequest;
use App\Http\Requests\UpdateFatigueInterviewRequest;
use App\Http\Resources\FatigueInterviewResource;
use App\Models\Employee;
use App\Models\FatigueInterview;
use App\Models\Setting;
use App\Services\FatigueInterviewService;
use App\Support\MenuAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FatigueInterviewController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private FatigueInterviewService $fatigueInterviewService) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $dateFrom = trim((string) $request->string('date_from', now()->startOfMonth()->toDateString()));
        $dateTo = trim((string) $request->string('date_to', now()->toDateString()));
        $page = max(1, (int) $request->integer('page', 1));

        $siteCode = Setting::getValue('fatigue_interview_site_code', 'ADW') ?: 'ADW';
        FatigueInterview::updateSiteCode($siteCode);

        $items = $this->fatigueInterviewService->paginate($search, $dateFrom ?: null, $dateTo ?: null, $page, self::PER_PAGE);

        return Inertia::render('OnePower/FatigueInterview/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'items' => FatigueInterviewResource::collection($items->items())->resolve(),
            'pagination' => [
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
                'total' => $items->total(),
            ],
            'canManage' => ! $request->user()?->isManagement(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/FatigueInterview/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
        ]);
    }

    public function store(StoreFatigueInterviewRequest $request): RedirectResponse
    {
        $this->fatigueInterviewService->create($request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/interview-fatigue')->with('success', 'Interview fatigue berhasil dibuat.');
    }

    public function edit(FatigueInterview $fatigueInterview): Response
    {
        $fatigueInterview->load(['employee', 'pengawas']);

        return Inertia::render('OnePower/FatigueInterview/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'targetItem' => (new FatigueInterviewResource($fatigueInterview))->resolve(),
        ]);
    }

    public function update(UpdateFatigueInterviewRequest $request, FatigueInterview $fatigueInterview): RedirectResponse
    {
        $this->fatigueInterviewService->update($fatigueInterview, $request->validated(), $request->user()?->id);

        return redirect('/one-power/pembinaan/interview-fatigue')->with('success', 'Interview fatigue berhasil diperbarui.');
    }

    public function destroy(FatigueInterview $fatigueInterview): RedirectResponse
    {
        $this->fatigueInterviewService->delete($fatigueInterview);

        return redirect()->back()->with('success', 'Interview fatigue berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isManagement(), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $result = $this->fatigueInterviewService->importFromExcel($request->file('file'), $request->user()?->id);

        $message = "Berhasil mengimport {$result['imported']} interview fatigue.";

        if ($result['invalidDates'] > 0) {
            $message .= " {$result['invalidDates']} baris memiliki tanggal tidak valid dan menggunakan tanggal hari ini — mohon periksa kembali.";
        }

        return redirect('/one-power/pembinaan/interview-fatigue')->with('success', $message);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'nrp', 'nama_operator', 'pengawas', 'nomor_unit', 'area', 'tanggal', 'jam',
            'q1_shift_day', 'q2_body_condition', 'q3_reason', 'q3_background',
            'q4_rest_hours', 'q5_rest_quality', 'q5_bad_quality_reason',
            'q6_home_comfort', 'q6_discomfort_reason', 'q7_other_activities',
            'q7_activity_details', 'q8_education', 'q9_heavy_fatigue_day',
            'q10_fit_plan', 'q11_suggestions',
        ];

        return ResponseFacade::streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, [
                '10001', 'Budi Santoso', '10002', 'DT 101', 'Hauling', date('Y-m-d'), '08:00',
                'Hari ke 3 shift siang', 'Lelah', 'Mengantuk saat operasi', 'Kurang tidur semalam',
                '5 jam', 'Kurang nyenyak', 'Bising sekitar', 'Cukup nyaman', '',
                'Tidak ada', '', 'Pernah, bulan lalu', 'Hari ke 4',
                'Tidur lebih awal dan hindari begadang', 'Penambahan waktu istirahat di rest area',
            ]);
            fclose($handle);
        }, 'template_interview_fatigue.csv', ['Content-Type' => 'text/csv']);
    }

    public function show(FatigueInterview $fatigueInterview): Response
    {
        $fatigueInterview->load(['employee', 'pengawas', 'creator', 'updater']);

        return Inertia::render('OnePower/FatigueInterview/Print', [
            'item' => (new FatigueInterviewResource($fatigueInterview))->resolve(),
            'documentSettings' => $this->getDocumentSettings(),
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && ! $user->isPimpinan())) {
            abort(403);
        }

        $validated = $request->validate([
            'siteCode' => ['required', 'string', 'max:20'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'noDokumen' => ['required', 'string', 'max:100'],
            'revisi' => ['nullable', 'string', 'max:50'],
            'tglEfektif' => ['nullable', 'date'],
        ]);

        $siteCode = strtoupper(trim($validated['siteCode']));
        $lokasi = trim($validated['lokasi'] ?? 'Wara');
        $noDokumen = trim($validated['noDokumen']);
        $revisi = trim($validated['revisi'] ?? '00');
        $tglEfektif = $validated['tglEfektif'] ?? '2022-09-12';

        Setting::setValue('fatigue_interview_site_code', $siteCode);
        Setting::setValue('fatigue_interview_lokasi', $lokasi);
        Setting::setValue('fatigue_interview_no_dokumen', $noDokumen);
        Setting::setValue('fatigue_interview_revisi', $revisi);
        Setting::setValue('fatigue_interview_tgl_efektif', $tglEfektif);

        FatigueInterview::updateSiteCode($siteCode);

        return redirect()->back()->with('success', 'Pengaturan format dokumen & no. surat interview fatigue berhasil dikunci.');
    }

    private function getDocumentSettings(): array
    {
        return [
            'siteCode' => Setting::getValue('fatigue_interview_site_code', 'ADW') ?: 'ADW',
            'lokasi' => Setting::getValue('fatigue_interview_lokasi', 'Wara') ?: 'Wara',
            'noDokumen' => Setting::getValue('fatigue_interview_no_dokumen', 'PPA-ADRO-F-PRO-06') ?: 'PPA-ADRO-F-PRO-06',
            'revisi' => Setting::getValue('fatigue_interview_revisi', '00') ?: '00',
            'tglEfektif' => Setting::getValue('fatigue_interview_tgl_efektif', '2022-09-12') ?: '2022-09-12',
        ];
    }

    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->string('keyword'));

        if ($keyword === '') {
            return response()->json([]);
        }

        $user = $request->user();

        // Row-level scoping: GL non-Manpower search dibatasi ke employee di unit mereka
        $employees = Employee::query()
            ->search($keyword)
            ->when(
                $user && !$user->isAdmin() && !$user->isPimpinan() && !$user->isGlManpower(),
                fn ($q) => $q->where('unit', $user->employee?->unit)
            )
            ->limit(10)
            ->get(['id', 'nrp', 'name', 'jabatan_ss6', 'unit']);

        return response()->json($employees);
    }
}
