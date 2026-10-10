<?php

namespace App\Services\Mor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Baris masukan MOR per operator untuk satu periode (ATR, Timesheet, HM, PDTY,
 * DPR) dan refresh snapshot Raport MOR. Port dari V1
 * database/quartal_operator_report/index.php (qorBuildOperatorRows, qorAtrPersen,
 * qorPembinaanMap, qorTimesheetMap, qorTimesheetMapGabungan, qorHmWorkdayKoreksi,
 * qorPdtyGabungan, morRefreshSnapshot) dan qor_pdty_engine.php (qorHmSupportBulk,
 * qorPdtyHmTotalBulk) serta mor_ranking_engine.php (qorSyncAtrFromV2/qorAutoSyncAtr).
 *
 * Perbedaan yang disengaja dari V1:
 * - ATR disinkron dari attendance_details (tabel absensi Laravel), bukan attendance_v2_detail.
 * - Cadangan qor_performa V1 (HM legacy, loader/jarak/unit per quartal) tidak dipakai:
 *   di Laravel tabel itu berisi ranking BCM tanpa NRP. Di V1 kolom HM-nya pun kosong,
 *   jadi poin tidak terpengaruh; hanya "Detail Produksi" quartal/tahun untuk operator
 *   tanpa DPR yang bisa berbeda.
 */
class MorInputBuilder
{
    /** Kode roster yang membuat hari itu tidak dihitung hari kerja untuk HM Target. */
    private const HM_KOREKSI_SHIFT_CODES = ['ID', 'MCU', 'IDS', 'FU'];

    /** HM Support (MOR: HM Actual = HM COAL + HM OB + HM Support), dari sheet LAIN2. */
    private const HM_SUPPORT_PROSES_CODES = ['P05', 'P06', 'P07', 'P09', 'P10'];

    private const HM_SUPPORT_AKTIVITAS_CODES = ['A11', 'A18'];

    /** Hari kerja x 7 HM/hari (LAIN2!F4 di MOR). */
    private const HM_PER_HARI = 7;

    private array $pdtyGabunganCache = [];

    public function __construct(private MorEngine $engine) {}

    // ── ATR ─────────────────────────────────────────────────────────────────

    /**
     * Tarik ATR dari attendance_details ke qor_atr_attendance: hanya posisi yang
     * diawali "OPERATOR", baris tanpa NRP/code dilewati. Upsert (idempotent).
     */
    public function syncAtr(string $start, string $end): int
    {
        return DB::affectingStatement("INSERT INTO qor_atr_attendance (nrp, nama, posisi, tanggal, code, dept, company)
            SELECT v.nrp, v.name, v.position, v.tanggal, v.code, v.dept, v.company
            FROM attendance_details v
            WHERE v.tanggal BETWEEN ? AND ?
              AND v.nrp IS NOT NULL AND v.nrp <> ''
              AND v.code IS NOT NULL AND v.code <> ''
              AND v.position LIKE 'OPERATOR%'
            ON DUPLICATE KEY UPDATE
              nama = VALUES(nama), posisi = VALUES(posisi), code = VALUES(code),
              dept = VALUES(dept), company = VALUES(company)", [$start, $end]);
    }

    /** Sinkron ATR maks 1x per $ttl detik per rentang (jejak di tabel settings). */
    public function autoSyncAtr(string $start, string $end, int $ttl = 600): void
    {
        $key = 'qor_atr_v2_sync_'.$start.'_'.$end;
        try {
            $last = DB::table('settings')->where('setting_key', $key)->value('updated_at');
            if ($last && (time() - strtotime($last)) < $ttl) {
                return;
            }
            $this->syncAtr($start, $end);
            DB::table('settings')->updateOrInsert(['setting_key' => $key], ['setting_value' => 'ok', 'updated_at' => now()]);
        } catch (Throwable $e) {
            Log::warning('[QOR] Auto sync ATR error: '.$e->getMessage());
        }
    }

    /**
     * ATR (%) sesuai laporan ATR HO: IO / (IO + S + I + A), IO = D + N + T
     * (training dihitung hadir), dibulatkan 1 desimal persen.
     */
    public static function atrPersen(array $row): float|int
    {
        $hadir = (int) $row['workday'] + (int) $row['training'];
        $total = $hadir + (int) $row['sakit'] + (int) $row['ijin'] + (int) $row['alpa'];

        return $total > 0 ? round($hadir / $total * 100, 1) : 0;
    }

    // ── Pembinaan & Timesheet ───────────────────────────────────────────────

    /**
     * TOTAL PEMBINAAN & status SP per NRP: CC+ x2, CC x-8, ST x-16, VI x-8, jenis
     * SURAT PERINGATAN PERTAMA/KEDUA/TERAKHIR x-24. Prasyarat gugur bila ada kode "SP...".
     */
    public function pembinaanMap(string $start, string $end): array
    {
        $map = [];
        $bobot = ['CC+' => 2, 'CC' => -8, 'ST' => -16, 'VI' => -8];
        $jenisSp = ['SURAT PERINGATAN PERTAMA', 'SURAT PERINGATAN KEDUA', 'SURAT PERINGATAN TERAKHIR'];
        foreach (DB::select('SELECT nrp, kode, jenis FROM qor_pembinaan WHERE tanggal BETWEEN ? AND ?', [$start, $end]) as $r) {
            $kode = strtoupper(trim((string) $r->kode));
            $m = &$map[$r->nrp];
            $m['total'] = ($m['total'] ?? 0) + ($bobot[$kode] ?? 0)
                + (in_array(strtoupper(trim((string) $r->jenis)), $jenisSp, true) ? -24 : 0);
            $m['has_sp'] = ($m['has_sp'] ?? false) || str_starts_with($kode, 'SP');
            unset($m);
        }

        return $map;
    }

    /**
     * Timesheet per NRP untuk satu periode. Bulan > 0: baris bulan itu, atau data
     * quartal lama (bulan = 0). Bulan = 0: data quartal lama, atau gabungan bulanan TRUE / TOTAL.
     */
    public function timesheetMap(int $tahun, int $quartal, int $bulan = 0): array
    {
        $map = [];
        $agg = [];
        $rows = DB::select('SELECT nrp, bulan, achievement, point, kategori, timesheet_true, total_timesheet
            FROM qor_timesheet WHERE tahun = ? AND quartal = ? ORDER BY bulan ASC', [$tahun, $quartal]);
        foreach ($rows as $o) {
            $r = (array) $o;
            $b = (int) $r['bulan'];
            if ($b === 0 || $b === $bulan) {
                $map[$r['nrp']] = $r;
            }
            if ($bulan == 0 && $b > 0) {
                $agg[$r['nrp']]['true'] = ($agg[$r['nrp']]['true'] ?? 0) + (int) $r['timesheet_true'];
                $agg[$r['nrp']]['total'] = ($agg[$r['nrp']]['total'] ?? 0) + (int) $r['total_timesheet'];
            }
        }
        foreach ($agg as $nrp => $a) {
            if (isset($map[$nrp]) || $a['total'] <= 0) {
                continue;
            }
            $map[$nrp] = ['nrp' => $nrp, 'bulan' => 0, 'achievement' => $a['true'] / $a['total'] * 100, 'point' => null, 'kategori' => null];
        }

        return $map;
    }

    /** Gabungan beberapa quartal: total TRUE / total TOTAL; data quartal lama hanya untuk quartal tanpa data bulanan. */
    public function timesheetMapGabungan(int $tahun, array $quartals): array
    {
        $ph = implode(',', array_fill(0, count($quartals), '?'));
        $per = [];
        $rows = DB::select("SELECT nrp, quartal, bulan, timesheet_true, total_timesheet FROM qor_timesheet WHERE tahun = ? AND quartal IN ($ph)", array_merge([$tahun], $quartals));
        foreach ($rows as $r) {
            $slot = (int) $r->bulan > 0 ? 'bulanan' : 'lama';
            $cur = $per[$r->nrp][$r->quartal][$slot] ?? [0, 0];
            $per[$r->nrp][$r->quartal][$slot] = [$cur[0] + (int) $r->timesheet_true, $cur[1] + (int) $r->total_timesheet];
        }
        $map = [];
        foreach ($per as $nrp => $qs) {
            $t = 0;
            $tot = 0;
            foreach ($qs as $slots) {
                [$a, $b] = $slots['bulanan'] ?? $slots['lama'];
                $t += $a;
                $tot += $b;
            }
            if ($tot > 0) {
                $map[$nrp] = ['nrp' => $nrp, 'bulan' => 0, 'achievement' => $t / $tot * 100, 'point' => null, 'kategori' => null];
            }
        }

        return $map;
    }

    // ── HM ──────────────────────────────────────────────────────────────────

    /**
     * Hari yang DIKURANGI dari workday per NRP untuk HM Target: hari ber-roster
     * ID/MCU/IDS/FU (Roster V2) yang absensinya D/N, seperti kolom Koreksi di SS6.
     */
    public function hmWorkdayKoreksi(string $start, string $end): array
    {
        $ph = implode(',', array_fill(0, count(self::HM_KOREKSI_SHIFT_CODES), '?'));
        try {
            $rows = DB::select("SELECT a.nrp, COUNT(DISTINCT a.tanggal) AS n
                FROM v2_roster_details d
                JOIN v2_roster_shifts s ON s.id = d.shift_id AND s.code IN ($ph)
                JOIN qor_atr_attendance a
                  ON a.nrp COLLATE utf8mb4_unicode_ci = d.nrp COLLATE utf8mb4_unicode_ci
                 AND a.tanggal = d.work_date
                WHERE d.work_date BETWEEN ? AND ?
                  AND LEFT(a.code, 1) IN ('D', 'N')
                GROUP BY a.nrp", array_merge(self::HM_KOREKSI_SHIFT_CODES, [$start, $end]));
        } catch (Throwable $e) {
            Log::warning('[QOR] Koreksi hari kerja dilewati: '.$e->getMessage());

            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[$r->nrp] = (int) $r->n;
        }

        return $out;
    }

    /** @return array<string, float> nrp => jam support */
    public function hmSupportBulk(string $start, string $end): array
    {
        $proses = implode(',', array_fill(0, count(self::HM_SUPPORT_PROSES_CODES), '?'));
        $aktivitas = implode(',', array_fill(0, count(self::HM_SUPPORT_AKTIVITAS_CODES), '?'));
        $rows = DB::select("SELECT nrp, SUM(jam) AS jam_support FROM (
                SELECT nrp, hour_time AS jam FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ? AND proses IN ($proses)
                UNION ALL
                SELECT nrp, hour_actv AS jam FROM qor_sap_hm WHERE tanggal BETWEEN ? AND ? AND aktivitas IN ($aktivitas)
            ) t GROUP BY nrp", array_merge([$start, $end], self::HM_SUPPORT_PROSES_CODES, [$start, $end], self::HM_SUPPORT_AKTIVITAS_CODES));
        $out = [];
        foreach ($rows as $r) {
            $out[$r->nrp] = (float) $r->jam_support;
        }

        return $out;
    }

    /** @return array<string, array<string, ?float>> nrp => ['COAL' => hm_total, 'OB' => hm_total] */
    public function pdtyHmTotalBulk(int $tahun, int $quartal, int $bulan = 0): array
    {
        $out = [];
        foreach (DB::select('SELECT nrp, kategori, hm_total FROM qor_pdty WHERE tahun = ? AND quartal = ? AND bulan = ?', [$tahun, $quartal, $bulan]) as $r) {
            $out[$r->nrp][$r->kategori] = $r->hm_total !== null ? (float) $r->hm_total : null;
        }

        return $out;
    }

    /**
     * Gabungan PDTY beberapa quartal (YTD): act/target ditimbang hm_total; null bila
     * ada quartal yang targetnya belum lengkap; Coal+OB digabung tertimbang HM.
     *
     * @return array{rows: array, hm: array}
     */
    public function pdtyGabungan(int $tahun, array $quartals): array
    {
        $key = $tahun.'|'.implode(',', $quartals);
        if (isset($this->pdtyGabunganCache[$key])) {
            return $this->pdtyGabunganCache[$key];
        }
        $ph = implode(',', array_fill(0, count($quartals), '?'));
        $agg = [];
        $rows = DB::select("SELECT nrp, kategori, SUM(hm_total) hm, SUM(sumproduct_act * hm_total) act, SUM(sumproduct_target * hm_total) tgt,
                SUM(achievement_pct IS NULL AND hm_total > 0) unresolved
            FROM qor_pdty WHERE tahun = ? AND bulan = 0 AND quartal IN ($ph) GROUP BY nrp, kategori", array_merge([$tahun], $quartals));
        foreach ($rows as $r) {
            $agg[$r->nrp][$r->kategori] = (array) $r;
        }
        $out = [];
        $hm = [];
        foreach ($agg as $nrp => $kat) {
            $pct = [];
            foreach (['COAL', 'OB'] as $k) {
                if (! isset($kat[$k])) {
                    continue;
                }
                $pct[$k] = (int) $kat[$k]['unresolved'] > 0 ? null
                    : ((float) $kat[$k]['tgt'] == 0 ? 0.0 : (float) $kat[$k]['act'] / (float) $kat[$k]['tgt']);
                $hm[$nrp][$k] = (float) $kat[$k]['hm'];
            }
            $combined = null;
            if (isset($kat['COAL'], $kat['OB']) && $pct['COAL'] !== null && $pct['OB'] !== null) {
                $den = $hm[$nrp]['COAL'] + $hm[$nrp]['OB'];
                $combined = $den == 0 ? 0.0 : ($pct['COAL'] * $hm[$nrp]['COAL'] + $pct['OB'] * $hm[$nrp]['OB']) / $den;
            }
            foreach ($pct as $k => $v) {
                $out[] = ['nrp' => $nrp, 'kategori' => $k, 'achievement_pct' => $v, 'achievement_combined' => $k === 'OB' ? $combined : null];
            }
        }

        return $this->pdtyGabunganCache[$key] = ['rows' => $out, 'hm' => $hm];
    }

    // ── Baris per operator ──────────────────────────────────────────────────

    /**
     * Baris per operator untuk satu periode. $pdtyBulan > 0 = satu bulan, 0 = agregat
     * quartal; $quartalGabungan (mis. [1,2,3,4]) = YTD.
     *
     * @return array{0: array, 1: array} [$rows, $timesheetByNrp]
     */
    public function buildOperatorRows(int $tahun, int $quartal, int $pdtyBulan, string $start, string $end, ?array $quartalGabungan = null, bool $syncAtr = true): array
    {
        if ($syncAtr) {
            $this->autoSyncAtr($start, $end);
        }
        $atrData = array_map(fn ($o) => (array) $o, DB::select("SELECT a.nrp,
                SUBSTRING_INDEX(GROUP_CONCAT(a.nama ORDER BY a.tanggal DESC SEPARATOR '|||'), '|||', 1) AS nama,
                SUBSTRING_INDEX(GROUP_CONCAT(a.posisi ORDER BY a.tanggal DESC SEPARATOR '|||'), '|||', 1) AS posisi,
                SUM(CASE WHEN LEFT(a.code, 1) IN ('D', 'N') THEN 1 ELSE 0 END) AS workday,
                SUM(CASE WHEN LEFT(a.code, 1) = 'S' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN LEFT(a.code, 1) = 'I' THEN 1 ELSE 0 END) AS ijin,
                SUM(CASE WHEN LEFT(a.code, 1) = 'A' THEN 1 ELSE 0 END) AS alpa,
                SUM(CASE WHEN LEFT(a.code, 1) = 'T' THEN 1 ELSE 0 END) AS training
            FROM qor_atr_attendance a
            WHERE a.tanggal BETWEEN ? AND ? AND a.posisi LIKE '%OPERATOR%'
            GROUP BY a.nrp
            ORDER BY nama ASC", [$start, $end]));

        $timesheetByNrp = $quartalGabungan
            ? $this->timesheetMapGabungan($tahun, $quartalGabungan)
            : $this->timesheetMap($tahun, $quartal, $pdtyBulan);

        // ACH PDTY: achievement_pct COAL/OB dan achievement_combined (baris OB).
        $pdtyRows = $quartalGabungan
            ? $this->pdtyGabungan($tahun, $quartalGabungan)['rows']
            : array_map(fn ($o) => (array) $o, DB::select('SELECT nrp, kategori, achievement_pct, achievement_combined
                FROM qor_pdty WHERE tahun = ? AND quartal = ? AND bulan = ?', [$tahun, $quartal, $pdtyBulan]));
        $pdtyByNrp = [];
        foreach ($pdtyRows as $p) {
            if ($p['kategori'] === 'COAL') {
                $pdtyByNrp[$p['nrp']]['coal'] = $p['achievement_pct'];
            } else {
                $pdtyByNrp[$p['nrp']]['ob'] = $p['achievement_pct'];
                $pdtyByNrp[$p['nrp']]['combined'] = $p['achievement_combined'];
            }
        }

        $dprByNrp = [];
        $dprRows = DB::select("SELECT nrp,
                GROUP_CONCAT(DISTINCT loader ORDER BY loader SEPARATOR ', ') as loader,
                ROUND(AVG(jarak), 1) as jarak,
                GROUP_CONCAT(DISTINCT kode_unit ORDER BY kode_unit SEPARATOR ', ') as kode_unit,
                SUM(rit) as rit,
                SUM(CASE WHEN source = 'OB' THEN rit END) as rit_ob,
                SUM(CASE WHEN source = 'COAL' THEN rit END) as rit_coal,
                SUM(total_bcm) as total_bcm,
                SUM(total_ton) as total_ton
            FROM qor_dpr WHERE tanggal BETWEEN ? AND ? GROUP BY nrp", [$start, $end]);
        foreach ($dprRows as $d) {
            $dprByNrp[$d->nrp] = (array) $d;
        }

        // HM Actual = HM(COAL) + HM(OB) dari qor_pdty + HM Support (SAP langsung).
        $pdtyHmByNrp = $quartalGabungan
            ? $this->pdtyGabungan($tahun, $quartalGabungan)['hm']
            : $this->pdtyHmTotalBulk($tahun, $quartal, $pdtyBulan);
        $hmSupportByNrp = $this->hmSupportBulk($start, $end);
        $hmKoreksiByNrp = $this->hmWorkdayKoreksi($start, $end);

        foreach ($atrData as &$row) {
            $row['atr_persen'] = self::atrPersen($row);

            $ts = $timesheetByNrp[$row['nrp']] ?? null;
            $row['timesheet_persen'] = $ts ? (float) $ts['achievement'] : null;
            $row['point'] = $ts ? (int) $ts['point'] : null;

            $dpr = $dprByNrp[$row['nrp']] ?? null;

            // HM Target = hari kerja (dikoreksi roster ID/MCU/IDS/FU) x 7. ATR tetap
            // memakai workday mentah -- di MOR itu kolom terpisah.
            $workdayHm = max(0, (int) $row['workday'] - ($hmKoreksiByNrp[$row['nrp']] ?? 0));
            $row['workday_hm'] = $workdayHm;
            $row['hm_target'] = $workdayHm * self::HM_PER_HARI;

            $pdtyHm = $pdtyHmByNrp[$row['nrp']] ?? null;
            $hmCoal = $pdtyHm['COAL'] ?? null;
            $hmOb = $pdtyHm['OB'] ?? null;
            $hmSupport = $hmSupportByNrp[$row['nrp']] ?? 0.0;
            $row['hm_coal'] = $hmCoal;
            $row['hm_ob'] = $hmOb;
            $row['hm_support'] = $hmSupport;

            // Tanpa hasil engine PDTY untuk periode ini, V1 jatuh ke HM legacy
            // qor_performa (kolomnya kosong di data V1) -> null.
            $row['hm'] = ($hmCoal !== null || $hmOb !== null) ? (float) $hmCoal + (float) $hmOb + $hmSupport : null;
            $row['hm_is_legacy'] = ($hmCoal === null && $hmOb === null);
            $row['hm_ach'] = ($row['hm_target'] > 0 && $row['hm'] !== null)
                ? round(($row['hm'] / $row['hm_target']) * 100)
                : null;

            $row['loader'] = ($dpr && ! empty($dpr['loader'])) ? $dpr['loader'] : null;
            $jarakVal = ($dpr && $dpr['jarak'] !== null) ? (float) $dpr['jarak'] : null;
            if ($jarakVal !== null && $jarakVal > 50) {
                $jarakVal /= 1000;
            }
            $row['jarak'] = $jarakVal !== null ? round($jarakVal, 2) : null;
            $row['kode_unit'] = ($dpr && ! empty($dpr['kode_unit'])) ? $dpr['kode_unit'] : null;
            $row['rit'] = ($dpr && $dpr['rit'] !== null) ? (float) $dpr['rit'] : null;
            $row['total_bcm'] = ($dpr && $dpr['total_bcm'] !== null) ? (float) $dpr['total_bcm'] : null;
            // BCM hanya di OB dan Ton hanya di COAL, jadi Rit dipisah per sumber.
            $row['rit_ob'] = ($dpr && $dpr['rit_ob'] !== null) ? (float) $dpr['rit_ob'] : null;
            $row['rit_coal'] = ($dpr && $dpr['rit_coal'] !== null) ? (float) $dpr['rit_coal'] : null;
            $row['total_ton'] = ($dpr && $dpr['total_ton'] !== null) ? (float) $dpr['total_ton'] : null;

            // qor_pdty menyimpan rasio (0.96) -- tampilkan sebagai persen.
            $pdty = $pdtyByNrp[$row['nrp']] ?? null;
            foreach (['coal', 'ob', 'combined'] as $pdtyKey) {
                $v = $pdty[$pdtyKey] ?? null;
                $row['pdty_'.$pdtyKey] = ($v === null) ? null : (float) $v * 100;
            }
        }
        unset($row);

        return [$atrData, $timesheetByNrp];
    }

    // ── Snapshot ────────────────────────────────────────────────────────────

    /** Hitung ulang snapshot satu periode: M (nomor = bulan), Q (nomor = quartal), Y (nomor = 0). */
    public function refreshSnapshot(string $periode, int $tahun, int $nomor, bool $syncAtr = true): void
    {
        [$start, $end] = MorEngine::periodRange($periode, $tahun, $nomor);
        if (! $this->engine->rangeHasProductionData($start, $end)) {
            $this->engine->deleteSnapshot($tahun, $nomor, $periode);

            return;
        }
        if ($periode !== 'M') {
            [$start, $end] = $this->engine->effectiveRange($start, $end);
        }
        if ($periode === 'M') {
            [$rows, $tsMap] = $this->buildOperatorRows($tahun, (int) ceil($nomor / 3), $nomor, $start, $end, null, $syncAtr);
        } elseif ($periode === 'Q') {
            [$rows, $tsMap] = $this->buildOperatorRows($tahun, $nomor, 0, $start, $end, null, $syncAtr);
        } else {
            [$rows, $tsMap] = $this->buildOperatorRows($tahun, 0, 0, $start, $end, [1, 2, 3, 4], $syncAtr);
        }
        $hasil = $this->engine->monthlyRanking(array_values($rows), $start, $end, $tsMap, $this->pembinaanMap($start, $end));
        $this->engine->saveSnapshot($tahun, $nomor, $hasil, $periode);
    }

    /**
     * Hitung ulang semua periode dari bulan yang ditandai berubah (settings.mor_raport_dirty):
     * bulan yang berubah ikut mengubah raport quartal & tahunnya.
     *
     * @return int jumlah periode yang dihitung
     */
    public function refreshDirty(): int
    {
        $dirty = json_decode((string) DB::table('settings')->where('setting_key', 'mor_raport_dirty')->value('setting_value'), true) ?: [];
        if (! $dirty) {
            return 0;
        }
        $targets = [];
        foreach ($dirty as $ym) {
            if (! preg_match('/^\d{4}-\d{2}$/', $ym)) {
                continue;
            }
            [$y, $m] = array_map('intval', explode('-', $ym));
            $q = (int) ceil($m / 3);
            $targets["M|$y|$m"] = ['M', $y, $m];
            $targets["Q|$y|$q"] = ['Q', $y, $q];
            $targets["Y|$y|0"] = ['Y', $y, 0];
        }
        foreach ($targets as [$p, $y, $n]) {
            $this->refreshSnapshot($p, $y, $n);
        }
        DB::table('settings')->where('setting_key', 'mor_raport_dirty')->delete();

        return count($targets);
    }
}
