<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * API mobile "MOR Saya": raport MOR milik user yang login, dibaca dari snapshot
 * mor_raport_bulanan (diisi mesin MOR). Padanan kartu V1 views/dashboard/_raport_mor.php;
 * tidak menghitung apa pun di sini.
 *
 * Query: ?raport=YYYY-MM (default raport terbaru, atau bulan lalu) & periode=M|Q|Y.
 */
class MorRaportController extends Controller
{
    private const URUT_KOMPONEN = ['POIN ATR', 'POIN HM', 'POIN PDTY', 'POIN TS', 'POIN PEMBINAAN', 'POIN FUEL'];

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()?->loadMissing('employee');
        $nrp = trim((string) ($user?->employee?->nrp ?: $user?->username));

        $bulanList = DB::table('mor_raport_bulanan')->where('nrp', $nrp)->where('periode', 'M')
            ->select('tahun', 'bulan')->distinct()->orderByDesc('tahun')->orderByDesc('bulan')->get()
            ->map(fn ($r) => sprintf('%04d-%02d', $r->tahun, $r->bulan))->values();

        $bulanIni = now()->format('Y-m');
        $ym = (string) $request->string('raport');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym) || $ym > $bulanIni) {
            $ym = $bulanList[0] ?? now()->startOfMonth()->subMonth()->format('Y-m');
        }
        [$tahun, $bulan] = array_map('intval', explode('-', $ym));

        // M = bulan terpilih, Q = quartal bulan itu, Y = tahun bulan itu.
        $periode = in_array($request->string('periode')->toString(), ['M', 'Q', 'Y'], true) ? $request->string('periode')->toString() : 'M';
        $quartal = (int) ceil($bulan / 3);
        $nomor = ['M' => $bulan, 'Q' => $quartal, 'Y' => 0][$periode];

        $step = ['M' => 1, 'Q' => 3, 'Y' => 12][$periode];
        $prev = date('Y-m', strtotime("$ym-01 -$step month"));
        $next = date('Y-m', strtotime("$ym-01 +$step month"));
        // "Berikutnya" mati kalau periodenya belum dimulai.
        $nextAktif = match ($periode) {
            'Y' => substr($next, 0, 4) <= date('Y'),
            'Q' => sprintf('%s-%02d', substr($next, 0, 4), (ceil((int) substr($next, 5, 2) / 3) - 1) * 3 + 1) <= $bulanIni,
            default => $next <= $bulanIni,
        };

        return response()->json([
            'nrp' => $nrp,
            'periode' => $periode,
            'ym' => $ym,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'quartal' => $quartal,
            'prev' => $prev,
            'next' => $nextAktif ? $next : null,
            'bulan_tersedia' => $bulanList,
            'raport' => $this->raport($nrp, $tahun, $periode, $nomor, $bulan, $quartal),
        ]);
    }

    private function raport(string $nrp, int $tahun, string $periode, int $nomor, int $bulan, int $quartal): ?array
    {
        $rows = DB::table('mor_raport_bulanan')
            ->where(['nrp' => $nrp, 'tahun' => $tahun, 'periode' => $periode, 'bulan' => $nomor])->get();
        if ($rows->isEmpty()) {
            return null;
        }
        // Operator PC 2000 juga cocok dengan pencarian "PC 200" -> ada di 2 sheet.
        // Pakai sheet yang pencariannya paling spesifik (terpanjang).
        $raport = $rows->sortByDesc(fn ($r) => strlen(json_decode($r->detail, true)['member_search'] ?? ''))->first();
        $detail = json_decode($raport->detail, true) ?: [];

        $komponen = [];
        foreach (self::URUT_KOMPONEN as $key) {
            if (! isset($detail['komponen'][$key])) {
                continue;
            }
            $def = $detail['komponen'][$key];
            $komponen[] = [
                'key' => $key,
                'input' => $def['input'],
                'nilai' => $detail['inputs'][$def['input']] ?? null,
                'poin' => (float) ($detail['poin'][$key] ?? 0),
                'maks' => (float) $def['maks'],
            ];
        }

        $timesheetAda = DB::table('qor_timesheet')->where('tahun', $tahun)
            ->when($periode === 'M', fn ($q) => $q->where('bulan', $bulan))
            ->when($periode === 'Q', fn ($q) => $q->where('quartal', $quartal))
            ->exists();

        $top = DB::table('mor_raport_bulanan')
            ->where(['tahun' => $tahun, 'periode' => $periode, 'bulan' => $nomor, 'sheet' => $raport->sheet])
            ->orderBy('ranking')->orderByDesc('total_poin')->limit(3)
            ->get(['nama', 'total_poin', 'ranking'])
            ->map(fn ($t) => ['nama' => $t->nama, 'total_poin' => (float) $t->total_poin, 'ranking' => (int) $t->ranking]);

        return [
            'sheet' => $raport->sheet,
            'jabatan' => $raport->jabatan,
            'ranking' => (int) $raport->ranking,
            'jumlah' => (int) $raport->jumlah,
            'total_poin' => (float) $raport->total_poin,
            'maks_total' => (float) array_sum(array_column($detail['komponen'] ?? [], 'maks')),
            'prasyarat_ok' => $raport->prasyarat === 'OK',
            'kategori' => $raport->kategori ?: null,
            'komponen' => $komponen,
            'prasyarat_gagal' => $detail['prasyarat_gagal'] ?? [],
            'produksi' => $detail['produksi'] ?? (object) [],
            'timesheet_ada' => $timesheetAda,
            'top' => $top,
            'computed_at' => $raport->computed_at,
        ];
    }
}
