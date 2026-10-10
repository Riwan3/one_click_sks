<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthApiController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $key = 'api-login|'.strtolower($data['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Terlalu banyak percobaan login gagal. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $user = User::query()->where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            $this->log($request, $user, $data['username'], 'FAILED_PASSWORD', 'Password salah (API)');

            return response()->json(['message' => 'NRP/Username atau password salah.'], 422);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            $this->log($request, $user, $user->username, 'ACCOUNT_INACTIVE', 'Akun non-aktif (API)');

            return response()->json(['message' => 'Akun Anda berstatus non-aktif. Silakan hubungi Administrator.'], 403);
        }

        RateLimiter::clear($key);
        $this->log($request, $user, $user->username, 'SUCCESS', null);

        $token = $user->createToken($data['device_name'] ?? 'flutter')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
        ];
    }

    private function log(Request $request, ?User $user, string $username, string $status, ?string $reason): void
    {
        LoginLog::create([
            'user_id' => $user?->id,
            'username' => $username,
            'nama' => $user?->name,
            'role' => $user?->role,
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
            'login_at' => now(),
        ]);
    }
}
