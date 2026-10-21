<?php

namespace App\Http\Controllers;

use App\Exceptions\PengajuanGradeException;
use App\Http\Requests\AjukanGradeRequest;
use App\Http\Requests\StorePengajuanGradeRequest;
use App\Http\Requests\UnlockOpdRequest;
use App\Http\Requests\UpdatePengajuanGradeRequest;
use App\Http\Resources\OpdPengajuanGradeResource;
use App\Models\OpdPengajuanGrade;
use App\Services\PengajuanGradeService;
use App\Support\MenuAccess;
use App\Support\OpdLock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OpdPengajuanGradeController extends Controller
{
    public function __construct(private PengajuanGradeService $service) {}

    public function index(Request $request): Response
    {
        $year = max(2000, min(2100, (int) $request->input('tahun', date('Y'))));

        $isLocked = ! OpdLock::isUnlocked($request);

        return Inertia::render('OneAcademy/Opd/PengajuanGrade', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'isLocked' => $isLocked,
            'year' => $year,
            'years' => $this->service->years($year),
            // Selama terkunci, data tidak dikirim ke browser sama sekali.
            'rows' => $isLocked ? [] : OpdPengajuanGradeResource::collection($this->service->rows($year))->resolve(),
            'usedTermins' => $isLocked ? [] : $this->service->usedTermins($year),
        ]);
    }

    public function unlock(UnlockOpdRequest $request): RedirectResponse
    {
        OpdLock::unlock($request);

        return back();
    }

    public function lock(Request $request): RedirectResponse
    {
        OpdLock::lock($request);

        return back();
    }

    /** Autocomplete untuk Tambah Data: karyawan dari Monitoring Grade. */
    public function searchEmployee(Request $request): JsonResponse
    {
        $keyword = trim($request->string('q')->toString());

        return response()->json(['data' => mb_strlen($keyword) < 2 ? [] : $this->service->searchMonitoring($keyword)]);
    }

    public function store(StorePengajuanGradeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return $this->respond(
            fn () => $this->service->ajukanFromMonitoring($data['nrp'], $data['termin'], $this->username(), $data['tahun']),
            'Pengajuan ditambahkan.',
        );
    }

    /** Dari Beranda OPD: ajukan karyawan Monitoring Grade pada termin di tahun berjalan. */
    public function ajukan(AjukanGradeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return $this->respond(
            fn () => $this->service->ajukanFromMonitoring($data['nrp'], $data['termin'], $this->username()),
            "Diajukan ke Pengajuan Grade termin {$data['termin']} tahun ".date('Y').'.',
        );
    }

    public function update(UpdatePengajuanGradeRequest $request, OpdPengajuanGrade $pengajuan): RedirectResponse
    {
        $this->service->updateField($pengajuan, $request->validated('field'), $request->validated('value'), $this->username());

        return back();
    }

    public function destroy(OpdPengajuanGrade $pengajuan): RedirectResponse
    {
        $pengajuan->delete();

        return back()->with('success', 'Pengajuan dihapus.');
    }

    private function respond(callable $action, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (PengajuanGradeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    private function username(): string
    {
        return (string) Auth::user()->username;
    }
}
