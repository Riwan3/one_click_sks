<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\QorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API mobile "Anak Asuh Monthly Report" untuk Group Leader: akumulasi DPR bulanan operator binaan
 * (tab monthly_report di QuartalOperatorReportController) dan rincian harian per operator.
 * Memakai QorService yang sama dengan halaman web.
 */
class QorApiController extends Controller
{
    public function __construct(private QorService $qor) {}

    /** ?quartal=1-4&tahun=&search= — baris per (operator, bulan) untuk anak asuh GL yang login. */
    public function monthly(Request $request): JsonResponse
    {
        $gl = $this->glOrFail($request);
        [$tahun, $quartal] = $this->period($request);
        $search = trim((string) $request->query('search', ''));

        $rows = $gl
            ? $this->qor->getMonthlyReport($tahun, $quartal, $search ?: null, glId: $gl->id)
            : collect();

        return response()->json([
            'tahun' => $tahun,
            'quartal' => $quartal,
            'rows' => $rows->map(fn ($r) => [
                'nrp' => (string) $r->nrp,
                'nama' => $r->nama,
                'jabatan' => $r->jabatan,
                'bulan' => (int) $r->bulan,
                'active_days' => (int) $r->active_days,
                'total_rit' => (float) $r->total_rit,
                'total_bcm' => (float) $r->total_bcm,
                'total_ton' => (float) $r->total_ton,
            ])->values(),
        ]);
    }

    /** ?nrp=&quartal=&tahun= — DPR harian satu operator; hanya untuk anak asuh GL yang login. */
    public function dprDaily(Request $request): JsonResponse
    {
        $gl = $this->glOrFail($request);
        $request->validate(['nrp' => ['required', 'string', 'max:30']]);
        [$tahun, $quartal] = $this->period($request);
        $nrp = (string) $request->query('nrp');

        $milik = $gl && Employee::where('nrp', $nrp)->where('gl_id', $gl->id)->exists();
        abort_unless($milik, 403, 'Operator ini bukan anak asuh Anda.');

        return response()->json([
            'nrp' => $nrp,
            'tahun' => $tahun,
            'quartal' => $quartal,
            'rows' => $this->qor->getDprDaily($nrp, $tahun, $quartal)->map(fn ($r) => [
                'tanggal' => (string) $r->tanggal,
                'role_op' => $r->role_op,
                'loader' => $r->loader,
                'kode_unit' => $r->kode_unit,
                'jarak' => (float) $r->jarak,
                'rit' => (float) $r->rit,
                'total_bcm' => (float) $r->total_bcm,
                'total_ton' => (float) $r->total_ton,
                'source' => $r->source,
            ])->values(),
        ]);
    }

    /** Hanya Group Leader (bukan admin); null bila belum punya data karyawan. */
    private function glOrFail(Request $request): ?Employee
    {
        $user = $request->user();
        abort_unless($user && $user->isGroupLeader() && ! $user->isAdmin(), 403, 'Menu ini hanya untuk Group Leader.');

        return $user->getEmployeeRecord();
    }

    /** @return array{0: int, 1: int} [tahun, quartal] (bawaan: kuartal berjalan menurut jam lokal) */
    private function period(Request $request): array
    {
        $now = now(config('app.local_timezone'));
        $quartal = (int) $request->query('quartal', (int) ceil($now->month / 3));
        $quartal = ($quartal >= 1 && $quartal <= 4) ? $quartal : (int) ceil($now->month / 3);
        $tahun = (int) $request->query('tahun', $now->year);

        return [max(2020, min(2100, $tahun)), $quartal];
    }
}
