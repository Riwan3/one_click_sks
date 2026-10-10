<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pendaftaran token FCM perangkat mobile untuk notifikasi push, plus pilihan user untuk
 * menerima/mematikan notifikasi. Token tidak dilepas saat logout (seperti app bank): HP tetap
 * terdaftar ke akun yang terakhir login sampai user mematikan notifikasi.
 */
class DeviceTokenController extends Controller
{
    /** Daftarkan (atau pindahkan ke user yang sedang login) token perangkat. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        if (! $this->enabled($request)) {
            return response()->json(['message' => 'Notifikasi dimatikan untuk akun ini.', 'registered' => false]);
        }

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? 'android',
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['message' => 'Token perangkat tersimpan.', 'registered' => true]);
    }

    /** Hapus token perangkat milik user yang sedang login. */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:512']]);

        DeviceToken::where('token', $data['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Token perangkat dihapus.']);
    }

    /** Apakah user menerima notifikasi push. */
    public function setting(Request $request): JsonResponse
    {
        return response()->json(['enabled' => $this->enabled($request)]);
    }

    /** Hidupkan/matikan notifikasi push untuk akun ini; mematikan juga melepas semua HP-nya. */
    public function updateSetting(Request $request): JsonResponse
    {
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $user = $request->user();
        $enabled = filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN);

        DB::table('users')->where('id', $user->id)->update(['push_enabled' => $enabled]);
        if (! $enabled) {
            DeviceToken::where('user_id', $user->id)->delete();
        }

        return response()->json(['enabled' => $enabled]);
    }

    private function enabled(Request $request): bool
    {
        return (bool) (DB::table('users')->where('id', $request->user()->id)->value('push_enabled') ?? true);
    }
}
