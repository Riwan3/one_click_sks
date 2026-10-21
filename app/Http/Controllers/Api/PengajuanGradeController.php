<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetPengajuanRemarkRequest;
use App\Http\Resources\OpdPengajuanGradeResource;
use App\Services\PengajuanGradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API untuk OneClick HO terhadap Pengajuan Grade di site ini (kontrak sama dengan api/pengajuan-grade.php versi lama):
 *   GET  ?action=ping
 *   GET  ?action=list&tahun=2026[&termin=1..4][&nrp=...]  -> daftar pengajuan (semua kolom + remark + warna)
 *   POST ?action=set_remark  body: nrp, tahun, termin, remark, warna (merah|kuning|hijau|kosong), by
 *        -> mengubah HANYA remark + warna pada pengajuan yang sudah ada.
 */
class PengajuanGradeController extends Controller
{
    /** action => [HTTP method, nama method] */
    private const ACTIONS = ['ping' => ['GET', 'ping'], 'list' => ['GET', 'list'], 'set_remark' => ['POST', 'setRemark']];

    public function __construct(private PengajuanGradeService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        [$verb, $method] = self::ACTIONS[$request->query('action')] ?? [null, null];
        if ($method === null || $request->method() !== $verb) {
            return response()->json(['success' => false, 'message' => 'Aksi tidak dikenal.'], 404);
        }

        return app()->call([$this, $method]);
    }

    public function ping(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'ok', 'server_time' => now()->toIso8601String()]);
    }

    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'termin' => ['nullable', 'integer', 'between:1,4'],
            'nrp' => ['nullable', 'string', 'max:50'],
        ], ['termin.between' => 'Termin harus 1 sampai 4.', 'tahun.*' => 'Tahun tidak valid.']);

        $items = $this->service->rows((int) $request->query('tahun'))
            ->when($request->filled('termin'), fn ($rows) => $rows->where('termin', (int) $request->query('termin')))
            ->when($request->filled('nrp'), fn ($rows) => $rows->where('nrp', trim($request->query('nrp'))))
            ->values();

        return response()->json([
            'success' => true,
            'tahun' => (int) $request->query('tahun'),
            'count' => $items->count(),
            'items' => OpdPengajuanGradeResource::collection($items)->resolve(),
        ]);
    }

    public function setRemark(SetPengajuanRemarkRequest $request): JsonResponse
    {
        $data = $request->validated();
        $by = rtrim('HO:'.trim((string) ($data['by'] ?? '')), ':');

        $found = $this->service->setRemark($data['nrp'], $data['tahun'], $data['termin'], $data['remark'] ?? null, $data['warna'] ?? null, $by);
        if (! $found) {
            return response()->json(['success' => false, 'message' => 'Pengajuan tidak ditemukan di site ini.'], 404);
        }

        return response()->json([
            'success' => true,
            'nrp' => $data['nrp'],
            'tahun' => $data['tahun'],
            'termin' => $data['termin'],
            'remark' => trim((string) ($data['remark'] ?? '')),
            'warna' => $data['warna'] ?? '',
        ]);
    }
}
