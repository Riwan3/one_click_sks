<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

// Port dari One-Click V1 (PDO + SQL V1 dipertahankan). Prepared statement; nama tabel/kolom hardcode.
class MoncuMonitoringRepository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? DB::connection()->getPdo();
    }

    private const JABATAN_SQL = "COALESCE(NULLIF(TRIM(e.jabatan_ss6), ''), NULLIF(TRIM(e.jabatan_roster), ''), '')";

    /** Karyawan MONCU aktif + data master (SELECT). Filter opsional: group (exact), jabatan (exact), q (NRP/nama LIKE). */
    public function listEmployees(array $f) {
        $where = ['m.is_active = 1']; $args = [];
        if (!empty($f['group']))   { $where[] = 'm.group_name = ?'; $args[] = $f['group']; }
        if (!empty($f['jabatan'])) { $where[] = self::JABATAN_SQL . ' = ?'; $args[] = $f['jabatan']; }
        if (!empty($f['q'])) {
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            $where[] = '(m.nrp LIKE ? OR e.name LIKE ?)';
            $args[] = $like; $args[] = $like;
        }
        $stmt = $this->db->prepare(
            "SELECT m.nrp, m.hiring_date, m.group_name, m.plan_interval_days, e.name AS employee_name,
                    " . self::JABATAN_SQL . " AS jabatan
             FROM v2_moncu_employees m
             LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             LEFT JOIN employees e ON e.id = x.mid
             WHERE " . implode(' AND ', $where) . "
             ORDER BY e.name, m.nrp"
        );
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function findEmployee($nrp) {
        $stmt = $this->db->prepare(
            "SELECT m.nrp, m.hiring_date, m.group_name, m.plan_interval_days, m.is_active, e.name AS employee_name,
                    " . self::JABATAN_SQL . " AS jabatan
             FROM v2_moncu_employees m
             LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
             LEFT JOIN employees e ON e.id = x.mid
             WHERE m.nrp = ?"
        );
        $stmt->execute([$nrp]);
        return $stmt->fetch() ?: null;
    }

    public function listGroups() {
        return $this->db->query(
            "SELECT DISTINCT group_name FROM v2_moncu_employees
             WHERE is_active = 1 AND group_name IS NOT NULL AND group_name <> '' ORDER BY group_name"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listJabatan() {
        return $this->db->query(
            "SELECT j FROM (
                SELECT DISTINCT " . self::JABATAN_SQL . " AS j
                FROM v2_moncu_employees m
                LEFT JOIN (SELECT nrp, MIN(id) AS mid FROM employees GROUP BY nrp) x ON x.nrp = m.nrp COLLATE utf8mb4_unicode_ci
                LEFT JOIN employees e ON e.id = x.mid
                WHERE m.is_active = 1
             ) t WHERE j <> '' ORDER BY j"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Semua periode (semua tahun) milik daftar NRP: [nrp => [rows]] - satu query per 500 NRP. */
    public function periodsForEmployees(array $nrps) {
        $out = [];
        foreach (array_chunk(array_values(array_unique($nrps)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT id, nrp, cycle_year, period_no, awal, tahunan, izin_resmi, akhir, induksi, plan
                 FROM v2_moncu_periods WHERE nrp IN ($in) ORDER BY nrp, cycle_year, period_no"
            );
            $stmt->execute($chunk);
            foreach ($stmt as $r) { $out[$r['nrp']][] = $r; }
        }
        return $out;
    }

    /**
     * Keanggotaan roster (karyawan punya >=1 detail) untuk daftar NRP pada roster yang periodenya
     * beririsan dengan [from, to]: [nrp => [ ['id','roster_group','status','period_start','period_end'], ... ]].
     * Hanya membaca v2_rosters / v2_roster_details.
     */
    public function rosterMemberships(array $nrps, $from, $to) {
        $out = [];
        foreach (array_chunk(array_values(array_unique($nrps)), 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT DISTINCT d.nrp, r.id, r.roster_group, r.status, r.period_start, r.period_end
                 FROM v2_roster_details d JOIN v2_rosters r ON r.id = d.roster_id
                 WHERE d.nrp IN ($in) AND r.period_end >= ? AND r.period_start <= ?"
            );
            $stmt->execute(array_merge($chunk, [$from, $to]));
            foreach ($stmt as $r) { $out[$r['nrp']][] = $r; }
        }
        return $out;
    }
}
