<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionSecurity
{
    /**
     * Handle an incoming request.
     * Memeriksa idle timeout, masa berlaku sesi, dan status keaktifan akun user saat request berlangsung.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $lastActivity = session('last_activity');
            $idleLifetime = (int) config('session.lifetime', 120) * 60; // detik

            // 1. Check Idle Timeout
            if ($lastActivity && (now()->timestamp - (int) $lastActivity > $idleLifetime)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'message' => 'Sesi Anda telah kedaluwarsa karena tidak ada aktivitas (Idle Timeout).',
                    ], 401);
                }

                return redirect('/login')->with('warning', 'Sesi Anda telah berakhir karena tidak ada aktivitas (Idle Timeout). Silakan login kembali.');
            }

            // 2. Check Account Status (Realtime Deactivation Check)
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
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect('/login')->with('error', 'Akun Anda telah dinonaktifkan oleh Administrator.');
                }
            }

            // 3. Update Last Activity Timestamp
            session(['last_activity' => now()->timestamp]);
        }

        return $next($request);
    }
}
