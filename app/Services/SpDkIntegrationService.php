<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SpDkLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpDkIntegrationService
{
    private string $apiUrl;

    private string $apiKey;

    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.spdk.url');
        $this->apiKey = config('services.spdk.key');
        $this->timeout = (int) config('services.spdk.timeout', 30);
    }

    public function lookupNrp(string $nrp): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->get("{$this->apiUrl}/lookup", ['nrp' => $nrp]);

            if ($response->failed()) {
                $this->logQuery($nrp, 'lookup', 'failed', $response->json());

                return null;
            }

            $data = $response->json();
            $this->logQuery($nrp, 'lookup', 'success', $data);

            return $data['data'] ?? null;
        } catch (\Exception $e) {
            Log::error('SPDK lookup error: ' . $e->getMessage(), ['nrp' => $nrp]);
            $this->logQuery($nrp, 'lookup', 'error', ['message' => $e->getMessage()]);

            return null;
        }
    }

    public function validateNrp(string $nrp): bool
    {
        $data = $this->lookupNrp($nrp);

        if (! $data) {
            return false;
        }

        return isset($data['nrp']) && ! empty($data['nrp']);
    }

    public function syncEmployeeData(Employee $employee): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->post("{$this->apiUrl}/sync", [
                    'nrp' => $employee->nrp,
                    'nama' => $employee->name,
                    'jabatan' => $employee->jabatan_ss6,
                    'departemen' => $employee->lokasi_kerja,
                    'unit' => $employee->unit,
                    'status' => $employee->status_karyawan,
                ]);

            if ($response->failed()) {
                $this->logQuery($employee->nrp, 'sync', 'failed', $response->json());

                return false;
            }

            $this->logQuery($employee->nrp, 'sync', 'success', $response->json());

            return true;
        } catch (\Exception $e) {
            Log::error('SPDK sync error: ' . $e->getMessage(), ['employee_id' => $employee->id]);
            $this->logQuery($employee->nrp, 'sync', 'error', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function getSpDkStatus(string $nrp): ?string
    {
        $data = $this->lookupNrp($nrp);

        if (! $data) {
            return null;
        }

        return $data['status'] ?? null;
    }

    private function logQuery(string $nrp, string $action, string $status, array $response): void
    {
        try {
            SpDkLog::create([
                'nrp' => $nrp,
                'action' => $action,
                'status' => $status,
                'response' => json_encode($response),
            ]);
        } catch (\Exception $e) {
            Log::error('SPDK log error: ' . $e->getMessage());
        }
    }
}
