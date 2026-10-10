<?php

namespace App\Services\Fcm;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Pengirim notifikasi push lewat Firebase Cloud Messaging HTTP v1.
 * Kredensial: file JSON service account (FCM_CREDENTIALS di .env). Tanpa kredensial, isConfigured()
 * bernilai false dan pemanggil melewati pengiriman. Tidak memakai paket tambahan (JWT ditandatangani
 * dengan ekstensi openssl).
 */
class FcmClient
{
    public const CHANNEL_ID = 'oneclick_default';

    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const TOKEN_CACHE_KEY = 'fcm.access_token';

    private ?array $credentials = null;

    public function __construct(private ?string $credentialsPath = null)
    {
        $this->credentialsPath ??= config('services.fcm.credentials');
    }

    public function isConfigured(): bool
    {
        return $this->credentialsPath !== null
            && $this->credentialsPath !== ''
            && is_file($this->credentialsPath);
    }

    /**
     * Kirim banyak pesan sekaligus (bersamaan, per kelompok).
     *
     * @param  array<int, array{token: string, title: string, body: string, data?: array<string, scalar>}>  $messages
     * @return array{sent: int, failed: int, invalid_tokens: array<int, string>}
     */
    public function sendMany(array $messages): array
    {
        $result = ['sent' => 0, 'failed' => 0, 'invalid_tokens' => []];
        if ($messages === [] || ! $this->isConfigured()) {
            return $result;
        }

        $accessToken = $this->accessToken();
        $url = 'https://fcm.googleapis.com/v1/projects/'.$this->credentials()['project_id'].'/messages:send';

        foreach (array_chunk($messages, 50) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $url, $accessToken) {
                return array_map(
                    fn (array $m) => $pool->withToken($accessToken)->timeout(15)->post($url, ['message' => $this->payload($m)]),
                    $chunk
                );
            });

            foreach ($chunk as $i => $m) {
                $response = $responses[$i] ?? null;
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $result['sent']++;

                    continue;
                }

                $result['failed']++;
                if ($response instanceof \Illuminate\Http\Client\Response && $this->isUnregistered($response)) {
                    $result['invalid_tokens'][] = $m['token'];
                } else {
                    Log::warning('FCM gagal kirim', ['status' => $response instanceof \Illuminate\Http\Client\Response ? $response->status() : null]);
                }
            }
        }

        return $result;
    }

    private function payload(array $m): array
    {
        return [
            'token' => $m['token'],
            'notification' => ['title' => $m['title'], 'body' => $m['body']],
            'data' => array_map('strval', $m['data'] ?? []),
            'android' => [
                'priority' => 'HIGH',
                'notification' => ['channel_id' => self::CHANNEL_ID],
            ],
        ];
    }

    private function isUnregistered(\Illuminate\Http\Client\Response $response): bool
    {
        if ($response->status() === 404) {
            return true;
        }
        foreach ((array) $response->json('error.details', []) as $detail) {
            if (($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                return true;
            }
        }

        return false;
    }

    private function credentials(): array
    {
        if ($this->credentials === null) {
            $data = json_decode((string) file_get_contents($this->credentialsPath), true);
            if (! is_array($data) || empty($data['client_email']) || empty($data['private_key']) || empty($data['project_id'])) {
                throw new RuntimeException('File kredensial FCM tidak valid.');
            }
            $this->credentials = $data;
        }

        return $this->credentials;
    }

    private function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function () {
            $c = $this->credentials();
            $now = time();
            $tokenUri = $c['token_uri'] ?? 'https://oauth2.googleapis.com/token';
            $claims = [
                'iss' => $c['client_email'],
                'scope' => self::SCOPE,
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ];
            $unsigned = $this->b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.$this->b64(json_encode($claims));
            if (! openssl_sign($unsigned, $signature, $c['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Gagal menandatangani JWT FCM.');
            }

            $response = Http::asForm()->timeout(15)->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned.'.'.$this->b64($signature),
            ]);
            if (! $response->successful() || ! $response->json('access_token')) {
                throw new RuntimeException('Gagal mengambil access token FCM (HTTP '.$response->status().').');
            }

            return $response->json('access_token');
        });
    }

    private function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
