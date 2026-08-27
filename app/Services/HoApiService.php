<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HoApiService
{
    /**
     * Fetch data from HO API endpoint.
     */
    public function fetchFromHoApi(string $endpoint, string $startDate, string $endDate, array $extraParams = []): array
    {
        $baseUrl = config('services.ho_api.url');
        $token = trim((string) config('services.ho_api.token'), " \t\n\r\0\x0B\"'");
        $timeout = (int) config('services.ho_api.timeout', 60);

        if (empty($baseUrl)) {
            return [
                'success' => false,
                'items' => [],
                'message' => 'HO_API_URL belum diisi di konfigurasi/env.',
            ];
        }

        $params = array_merge([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ], $extraParams);

        $url = rtrim(strtok($baseUrl, '?'), '/').'/'.ltrim($endpoint, '/');

        try {
            $response = Http::timeout($timeout)
                ->withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])
                ->get($url, $params);

            if (! $response->successful()) {
                Log::warning('HO API HTTP Error', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 250),
                ]);

                return [
                    'success' => false,
                    'items' => [],
                    'message' => "HTTP {$response->status()}: ".substr($response->body(), 0, 200),
                ];
            }

            $data = $response->json();
            $items = is_array($data['data'] ?? null) ? $data['data'] : [];

            return [
                'success' => true,
                'items' => $items,
                'message' => count($items)." data ditemukan (periode {$startDate} - {$endDate})",
            ];
        } catch (\Throwable $e) {
            Log::error('HO API Connection Error', ['exception' => $e->getMessage()]);

            return [
                'success' => false,
                'items' => [],
                'message' => 'Gagal koneksi HO API: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Fetch HM (Hour Meter) data from HO API (endpoint: opt-hm).
     */
    public function fetchHmFromApi(string $startDate, string $endDate, string $shift = 'all'): array
    {
        return $this->fetchFromHoApi('opt-hm', $startDate, $endDate, ['shift' => $shift]);
    }

    /**
     * Fetch Attendance V2 detail data from HO API (endpoint: attendance-detail).
     */
    public function fetchAttendanceV2FromApi(string $startDate, string $endDate, string $dept = 'PRO'): array
    {
        return $this->fetchFromHoApi('attendance-detail', $startDate, $endDate, ['dept' => $dept]);
    }

    /**
     * Classify attendance code based on initial letter:
     * D = Siang, N = Malam, S = Sakit, I = Izin, A = Alpa.
     */
    public function classifyAttendanceCode(?string $code): ?string
    {
        if (! $code) {
            return null;
        }

        $prefix = strtoupper(substr(trim($code), 0, 1));

        return in_array($prefix, ['D', 'N', 'S', 'I', 'A'], true) ? $prefix : null;
    }
}
