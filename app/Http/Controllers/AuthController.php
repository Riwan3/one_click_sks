<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        // 1. Cek apakah akun sedang terkunci karena brute-force (Account Locked)
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            LoginLog::create([
                'username' => $request->string('username'),
                'status' => 'ACCOUNT_LOCKED',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => "Terlalu banyak percobaan gagal (Lockout {$seconds} detik)",
                'login_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan login gagal. Akun dikunci sementara demi keamanan. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // 2. Cek apakah User ada di sistem
        $user = User::query()->where('username', $credentials['username'])->first();

        // 3. Cek Status Keaktifan Akun (Account Active Check)
        if ($user) {
            $isInactive = false;
            if (isset($user->is_active) && ! $user->is_active) {
                $isInactive = true;
            } elseif ($user->employee) {
                $empStatus = $user->employee->status_karyawan;
                $statusVal = $empStatus instanceof \BackedEnum ? $empStatus->value : (string) $empStatus;
                if (in_array(strtoupper((string) $statusVal), ['INACTIVE', 'NON AKTIF', 'INAKTIF', 'RESIGN', 'TERMINATED'], true)) {
                    $isInactive = true;
                }
            }

            if ($isInactive) {
                LoginLog::create([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'nama' => $user->name,
                    'role' => $user->role,
                    'status' => 'ACCOUNT_INACTIVE',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'reason' => 'Akun karyawan non-aktif',
                    'login_at' => now(),
                ]);

                throw ValidationException::withMessages([
                    'username' => 'Akun Anda berstatus non-aktif. Silakan hubungi Administrator.',
                ]);
            }
        }

        // 4. Verifikasi Password (Password Hashing / Bcrypt check)
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            $remaining = RateLimiter::remaining($throttleKey, self::MAX_ATTEMPTS);

            LoginLog::create([
                'user_id' => $user?->id,
                'username' => $request->string('username'),
                'nama' => $user?->name,
                'role' => $user?->role,
                'status' => 'FAILED_PASSWORD',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => "Password salah (Sisa percobaan: {$remaining})",
                'login_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'username' => "NRP/Username atau password salah. (Sisa percobaan: {$remaining})",
            ]);
        }

        // 5. Reset Rate Limiter saat sukses login
        RateLimiter::clear($throttleKey);

        // 6. Session Regeneration (Mencegah session fixation) & Metadata Session
        $request->session()->regenerate();

        $authenticatedUser = Auth::user();

        // Simpan metadata sesi aktif
        $request->session()->put([
            'login_at' => now()->toDateTimeString(),
            'last_activity' => now()->timestamp,
            'login_ip' => $request->ip(),
            'login_user_agent' => $request->userAgent(),
        ]);

        // 7. Audit Login (Pencatatan sesi berhasil)
        LoginLog::create([
            'user_id' => $authenticatedUser->id,
            'username' => $authenticatedUser->username,
            'nama' => $authenticatedUser->name,
            'role' => $authenticatedUser->role,
            'status' => 'SUCCESS',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
        ]);

        return redirect('/portal');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            LoginLog::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'nama' => $user->name,
                'role' => $user->role,
                'status' => 'LOGOUT',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logout_at' => now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Daftar Sesi Aktif (Active Sessions) - Untuk Admin / User
     */
    public function activeSessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        $query = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select([
                'sessions.id',
                'sessions.user_id',
                'users.name as user_name',
                'users.username as nrp',
                'users.role',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
            ])
            ->orderBy('sessions.last_activity', 'desc');

        if (! $isAdmin) {
            $query->where('sessions.user_id', $user->id);
        }

        $sessions = $query->limit(50)->get()->map(function ($s) {
            $s->is_current = ($s->id === session()->getId());
            $s->last_activity_human = \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans();
            return $s;
        });

        return response()->json([
            'status' => 'success',
            'sessions' => $sessions,
        ]);
    }

    /**
     * Force Logout / Putus Sesi (Admin can force logout any session, user can revoke other devices)
     */
    public function revokeSession(Request $request, string $sessionId): JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        $sessionQuery = DB::table('sessions')->where('id', $sessionId);
        if (! $isAdmin) {
            $sessionQuery->where('user_id', $user->id);
        }

        $deleted = $sessionQuery->delete();

        return response()->json([
            'status' => $deleted ? 'success' : 'not_found',
            'message' => $deleted ? 'Sesi berhasil diputus (Force Logout).' : 'Sesi tidak ditemukan.',
        ]);
    }

    /**
     * Buka Kunci Akun (Admin Unlock)
     */
    public function unlockAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin()
            || ($user && in_array(strtolower((string) $user->role), ['admin', 'admin_produksi'], true))
            || ($user && str_contains(strtolower((string) $user->jabatan), 'admin'));

        abort_unless($isAdmin, 403, 'Akses ditolak. Hanya Admin yang dapat membuka lock akun.');

        $username = trim((string) $request->input('username'));
        if (! $username) {
            return response()->json(['status' => 'error', 'message' => 'Username/NRP wajib diisi.'], 422);
        }

        RateLimiter::clear(self::getThrottleKey($username));

        return response()->json([
            'status' => 'success',
            'message' => "Akun {$username} berhasil di-unlock. User kini dapat mencoba login kembali.",
        ]);
    }

    public static function getThrottleKey(string $username): string
    {
        return 'login_lock_' . Str::transliterate(Str::lower(trim($username)));
    }

    private function throttleKey(Request $request): string
    {
        return self::getThrottleKey((string) $request->input('username'));
    }
}
