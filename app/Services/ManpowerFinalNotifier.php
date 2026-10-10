<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use App\Services\Fcm\FcmClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Notifikasi push ke operator saat Setting Manpower (v2.01) difinalkan:
 * "Selamat pagi — Anda tersetting di unit X dengan PC Y di CSA Z."
 * Finalkan ulang setelah Reopen hanya memberi tahu operator yang penempatannya baru/berubah.
 */
class ManpowerFinalNotifier
{
    public function __construct(private FcmClient $fcm) {}

    /**
     * @return array{notified: int, skipped_unchanged: int, no_device: int, sent: int, failed: int}|null null bila FCM belum dikonfigurasi atau setting bukan final
     */
    public function notify(int $settingId): ?array
    {
        if (! $this->fcm->isConfigured()) {
            Log::info('Notifikasi final manpower dilewati: FCM belum dikonfigurasi.');

            return null;
        }

        $setting = DB::table('v2_mp_settings')->where('id', $settingId)->first();
        if (! $setting || $setting->status !== 'final') {
            return null;
        }

        $assignments = $this->assignments($settingId);
        $pcByUnit = $this->pcByUnit($setting->work_date, $setting->shift, $assignments->pluck('unit')->all());

        $current = [];
        foreach ($assignments as $a) {
            $pc = $pcByUnit[$this->key($a->unit)] ?? null;
            $current[$a->nrp] = [
                'unit' => $a->unit,
                'csa' => $a->csa,
                'pc' => $pc,
                'signature' => $a->unit.'|'.$a->csa.'|'.($pc ?? ''),
            ];
        }

        $previous = DB::table('manpower_final_notifications')
            ->where('setting_id', $settingId)
            ->pluck('signature', 'nrp')
            ->all();

        $toNotify = array_filter($current, fn (array $c, string $nrp) => ($previous[$nrp] ?? null) !== $c['signature'], ARRAY_FILTER_USE_BOTH);

        $users = User::whereIn('username', array_keys($toNotify))->pluck('id', 'username');
        $tokens = DeviceToken::whereIn('user_id', $users->values())->get()->groupBy('user_id');

        $greeting = $setting->shift === 'N' ? 'Selamat sore' : 'Selamat pagi';
        $messages = [];
        $noDevice = 0;
        foreach ($toNotify as $nrp => $c) {
            $userTokens = $tokens->get($users[$nrp] ?? 0, collect());
            if ($userTokens->isEmpty()) {
                $noDevice++;

                continue;
            }
            foreach ($userTokens as $t) {
                $messages[] = [
                    'token' => $t->token,
                    'title' => $greeting,
                    'body' => $this->body($c),
                    'data' => [
                        'type' => 'manpower_final',
                        'work_date' => (string) $setting->work_date,
                        'shift' => (string) $setting->shift,
                        'unit' => $c['unit'],
                    ],
                ];
            }
        }

        $result = $this->fcm->sendMany($messages);
        if ($result['invalid_tokens']) {
            DeviceToken::whereIn('token', $result['invalid_tokens'])->delete();
        }

        $this->saveSnapshot($settingId, $current);

        return [
            'notified' => count($toNotify) - $noDevice,
            'skipped_unchanged' => count($current) - count($toNotify),
            'no_device' => $noDevice,
            'sent' => $result['sent'],
            'failed' => $result['failed'],
        ];
    }

    private function body(array $c): string
    {
        return 'Anda tersetting di unit '.$c['unit']
            .($c['pc'] ? ' dengan PC '.$c['pc'] : '')
            .' di '.$c['csa'].'.';
    }

    private function assignments(int $settingId)
    {
        return DB::table('v2_mp_assignments as a')
            ->join('v2_mp_units as u', 'u.id', '=', 'a.unit_id')
            ->join('v2_mp_categories as c', 'c.id', '=', 'u.category_id')
            ->join('v2_mp_csa as s', 's.id', '=', 'c.csa_id')
            ->where('a.setting_id', $settingId)
            ->select('a.nrp', 'u.unit', 's.name as csa')
            ->get();
    }

    /**
     * PC (excavator/loader) untuk tiap unit dari Setting Fleet tanggal & shift yang sama
     * (fleet_allocations: shift 1 = D, 2 = N). Unit tanpa alokasi fleet tidak punya PC.
     *
     * @param  array<int, string>  $units
     * @return array<string, string>
     */
    private function pcByUnit(string $date, string $shift, array $units): array
    {
        $rows = DB::table('fleet_allocations')
            ->where('tanggal', $date)
            ->where('shift', $shift === 'N' ? 2 : 1)
            ->whereIn(DB::raw('UPPER(TRIM(unit_nrp))'), array_map($this->key(...), $units))
            ->whereNotNull('loader_id')
            ->where('group_name', 'not like', '%BREAKDOWN%')
            ->orderBy('id')
            ->get(['unit_nrp', 'loader_id']);

        $map = [];
        foreach ($rows as $r) {
            $map[$this->key($r->unit_nrp)] ??= trim($r->loader_id);
        }

        return $map;
    }

    private function key(string $unit): string
    {
        return strtoupper(trim($unit));
    }

    private function saveSnapshot(int $settingId, array $current): void
    {
        DB::transaction(function () use ($settingId, $current) {
            DB::table('manpower_final_notifications')
                ->where('setting_id', $settingId)
                ->when($current !== [], fn ($q) => $q->whereNotIn('nrp', array_keys($current)))
                ->delete();

            $now = now();
            foreach ($current as $nrp => $c) {
                DB::table('manpower_final_notifications')->updateOrInsert(
                    ['setting_id' => $settingId, 'nrp' => (string) $nrp],
                    ['signature' => $c['signature'], 'notified_at' => $now, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        });
    }
}
