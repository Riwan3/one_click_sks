<?php

namespace App\Http\Controllers;

use App\Models\SpDkLog;
use App\Services\SpDkIntegrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpDkController extends Controller
{
    public function __construct(private SpDkIntegrationService $spDkService) {}

    public function lookup(Request $request)
    {
        $request->validate(['nrp' => 'required|string']);

        $nrp = $request->input('nrp');
        $data = $this->spDkService->lookupNrp($nrp);

        if (! $data) {
            return response()->json([
                'valid' => false,
                'nama' => null,
                'status' => null,
                'message' => 'NRP tidak ditemukan di SPDK',
            ]);
        }

        $statusBadge = match ($data['status'] ?? null) {
            'active' => '🟢 Aktif',
            'expiring_soon' => '🟡 Akan Hangus',
            'expired' => '🔴 Hangus',
            default => '⚪ Unknown',
        };

        return response()->json([
            'valid' => true,
            'nama' => $data['nama'] ?? null,
            'status' => $data['status'] ?? null,
            'status_badge' => $statusBadge,
            'message' => 'Data ditemukan',
        ]);
    }

    public function syncLog(Request $request)
    {
        $query = SpDkLog::query()->latest();

        if ($request->has('nrp')) {
            $query->where('nrp', $request->input('nrp'));
        }

        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->paginate(20);

        return Inertia::render('SpDk/SyncLog', [
            'logs' => $logs,
            'filters' => [
                'nrp' => $request->input('nrp'),
                'action' => $request->input('action'),
                'status' => $request->input('status'),
            ],
        ]);
    }
}
