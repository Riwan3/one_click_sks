<?php

namespace App\Http\Middleware;

use App\Services\SpDkIntegrationService;
use Closure;
use Illuminate\Http\Request;

class ValidateSpDkForDocument
{
    public function __construct(private SpDkIntegrationService $spDkService) {}

    public function handle(Request $request, Closure $next)
    {
        $nrp = $request->input('employee_nrp') ?? $request->input('nrp');

        if (! $nrp) {
            return $next($request);
        }

        $status = $this->spDkService->getSpDkStatus($nrp);

        if ($status === null) {
            return back()->withErrors(['employee_nrp' => 'NRP tidak ditemukan di SPDK.']);
        }

        if ($status !== 'active') {
            return back()->withErrors([
                'employee_nrp' => "Izin kerja karyawan tidak aktif (Status: {$status}). Dokumen tidak dapat dibuat.",
            ]);
        }

        return $next($request);
    }
}
