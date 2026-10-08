<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

// Port One-Click V1 (PDO + SQL V1 dipertahankan). WRITE hanya ke v2_mp_settings / v2_mp_assignments / v2_mp_released / v2_mp_lain; Roster V2 & employees hanya di-SELECT.
class ManpowerV201SettingRepository {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? DB::connection()->getPdo();
    }


    public function transaction(callable $fn) {
        if ($this->db->inTransaction()) {
            return $fn();
        }
        $this->db->beginTransaction();
        try {
            $r = $fn();
            $this->db->commit();
            return $r;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function tablesReady() {
        try {
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME IN ('v2_mp_settings','v2_mp_assignments','v2_mp_released','v2_mp_units')"
            );
            return (int)$stmt->fetchColumn() === 4;
        } catch (Throwable $e) {
            Log::error('[ManpowerV201] cek tabel setting gagal: ' . $e->getMessage());
            return false;
        }
    }

    // ------------------------------------------------------- Roster V2 (baca)

    /**
     * Orang yang hadir (kode shift = $shiftCode, 'D' atau 'N') pada $date menurut Roster V2.
     * Hanya roster berstatus published yang dipakai (draft & archived diabaikan; mis. roster 2026 yang
     * sudah di-archive setelah roster 2027 dipublish tidak ikut). Satu baris per NRP.
     * Kunci hasil: NRP. Field: nrp, name, unit (kolom unit anggota roster), section, phone, jabatan (SS6).
     */
    public function attendance($date, $shiftCode) {
        $stmt = $this->db->prepare(
            "SELECT d.nrp,
                    COALESCE(NULLIF(TRIM(m.employee_name), ''), e.name, '') AS name,
                    TRIM(COALESCE(m.unit, '')) AS unit,
                    COALESCE(sec.name, '') AS section,
                    COALESCE(e.no_telepon, '') AS phone,
                    COALESCE(e.jabatan_ss6, m.jabatan, '') AS jabatan
             FROM v2_roster_details d
             JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
             JOIN v2_roster_shifts s ON s.id = d.shift_id AND s.code = ?
             JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             LEFT JOIN v2_roster_sections sec ON sec.id = m.section_id
             LEFT JOIN (
                 SELECT nrp, MAX(name) AS name, MAX(no_telepon) AS no_telepon, MAX(jabatan_ss6) AS jabatan_ss6
                 FROM employees GROUP BY nrp
             ) e ON e.nrp COLLATE utf8mb4_general_ci = d.nrp
             WHERE d.work_date = ?
             ORDER BY m.unit, d.nrp"
        );
        $stmt->execute([$shiftCode, $date]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!isset($out[$row['nrp']])) $out[$row['nrp']] = $row;
        }
        return $out;
    }

    /**
     * Orang yang pada $date berstatus ID (induksi), TR (training) atau IDS (induksi permit) menurut Roster V2
     * (hanya roster published). Untuk kotak LAIN-LAIN; berlaku untuk shift mana pun. Urut: ID, TR, IDS, lalu nama.
     * Field: nrp, name, unit, phone, jabatan, code.
     */
    public function otherStatus($date) {
        $stmt = $this->db->prepare(
            "SELECT d.nrp,
                    COALESCE(NULLIF(TRIM(m.employee_name), ''), e.name, '') AS name,
                    TRIM(COALESCE(m.unit, '')) AS unit,
                    COALESCE(e.no_telepon, '') AS phone,
                    COALESCE(e.jabatan_ss6, m.jabatan, '') AS jabatan,
                    s.code AS code
             FROM v2_roster_details d
             JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
             JOIN v2_roster_shifts s ON s.id = d.shift_id AND s.code IN ('ID', 'TR', 'IDS')
             JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             LEFT JOIN (
                 SELECT nrp, MAX(name) AS name, MAX(no_telepon) AS no_telepon, MAX(jabatan_ss6) AS jabatan_ss6
                 FROM employees GROUP BY nrp
             ) e ON e.nrp COLLATE utf8mb4_general_ci = d.nrp
             WHERE d.work_date = ?
             ORDER BY FIELD(s.code, 'ID', 'TR', 'IDS'), name"
        );
        $stmt->execute([$date]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!isset($out[$row['nrp']])) $out[$row['nrp']] = $row;
        }
        return array_values($out);
    }

    // --------------------------------------------------- Kode roster & SS6

    /**
     * Kode roster (D, N, OFF, CT, TR, ID, IDS, ...) pada $date untuk daftar NRP, dari Roster V2 published.
     * Return: [nrp => kode]. NRP tanpa jadwal tidak ada di hasil.
     */
    public function rosterCodes($date, array $nrps) {
        $nrps = array_values(array_unique(array_map('strval', $nrps)));
        $out = [];
        foreach (array_chunk($nrps, 400) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $this->db->prepare(
                "SELECT d.nrp, s.code
                 FROM v2_roster_details d
                 JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
                 JOIN v2_roster_shifts s ON s.id = d.shift_id
                 JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
                 WHERE d.work_date = ? AND d.nrp IN ($in)"
            );
            $stmt->execute(array_merge([$date], $chunk));
            foreach ($stmt->fetchAll() as $row) {
                if (!isset($out[$row['nrp']])) $out[(string)$row['nrp']] = $row['code'];
            }
        }
        return $out;
    }

    /** Jumlah operator (jabatan SS6 memuat OPERATOR) yang dipasang ke unit pada tanggal itu, semua shift, per NRP. GL, driver, & lain-lain tidak dipasang ke unit. */
    public function operatorCountOn($date) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT a.nrp)
             FROM v2_mp_assignments a
             JOIN v2_mp_settings s ON s.id = a.setting_id
             JOIN (SELECT nrp, MAX(jabatan_ss6) AS jabatan_ss6 FROM employees GROUP BY nrp) e
               ON e.nrp COLLATE utf8mb4_general_ci = a.nrp
             WHERE s.work_date = ? AND e.jabatan_ss6 LIKE '%OPERATOR%'"
        );
        $stmt->execute([$date]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Kode SS6 (kolom code: DR, DE, NR, OL, CR, TR, ...) pada $date untuk daftar NRP, dari One-Power > Personal Data > Attendance
     * (tabel attendance_details, sumber halaman One-Power > Personal Data > Attendance). Return: [nrp => ['code' =>, 'job' =>, 'jam_in' =>]].
     * Tabel belum ada / tidak terbaca -> kosong.
     */
    public function ss6Codes($date, array $nrps) {
        $nrps = array_values(array_unique(array_map('strval', $nrps)));
        $out = [];
        try {
            foreach (array_chunk($nrps, 400) as $chunk) {
                $in = implode(',', array_fill(0, count($chunk), '?'));
                $stmt = $this->db->prepare(
                    "SELECT nrp, code, job, jam_in FROM attendance_details
                     WHERE tanggal = ? AND nrp IN ($in) ORDER BY id"
                );
                $stmt->execute(array_merge([$date], $chunk));
                foreach ($stmt->fetchAll() as $row) {
                    $out[(string)$row['nrp']] = ['code' => (string)$row['code'], 'job' => (string)$row['job'], 'jam_in' => (string)$row['jam_in']];
                }
            }
        } catch (Throwable $e) {
            Log::error('[ManpowerV201] baca SS6 gagal: ' . $e->getMessage());
            return [];
        }
        return $out;
    }

    // ------------------------------------------------------------ Monitoring

    /**
     * Jumlah orang hadir (kode D/N) per tanggal + shift menurut Roster V2 published, dalam rentang tanggal.
     * Return: ['2026-10-01' => ['D' => 32, 'N' => 32], ...]. Hanya tanggal yang ada isinya.
     */
    public function rosterHadirByDate($from, $to) {
        $stmt = $this->db->prepare(
            "SELECT d.work_date, s.code, COUNT(DISTINCT d.nrp) AS n
             FROM v2_roster_details d
             JOIN v2_rosters r ON r.id = d.roster_id AND r.status = 'published'
             JOIN v2_roster_shifts s ON s.id = d.shift_id AND s.code IN ('D', 'N')
             JOIN v2_roster_members m ON m.roster_id = d.roster_id AND m.nrp = d.nrp
             WHERE d.work_date BETWEEN ? AND ?
             GROUP BY d.work_date, s.code"
        );
        $stmt->execute([$from, $to]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) $out[$row['work_date']][$row['code']] = (int)$row['n'];
        return $out;
    }

    /** Semua setting dalam rentang tanggal. */
    public function settingsBetween($from, $to) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_settings WHERE work_date BETWEEN ? AND ? ORDER BY work_date, shift");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    /** Nama karyawan menurut NRP/username (untuk 'diubah oleh'). Kunci hasil: nrp. */
    public function employeeNames(array $nrps) {
        $nrps = array_values(array_unique(array_filter(array_map('strval', $nrps), 'strlen')));
        if (!$nrps) return [];
        $in = implode(',', array_fill(0, count($nrps), '?'));
        $stmt = $this->db->prepare(
            "SELECT nrp, MAX(name) AS name FROM employees WHERE nrp COLLATE utf8mb4_general_ci IN ($in) GROUP BY nrp"
        );
        $stmt->execute($nrps);
        $out = [];
        foreach ($stmt->fetchAll() as $row) $out[(string)$row['nrp']] = $row['name'];
        return $out;
    }

    // --------------------------------------------------------------- Setting

    public function findSetting($date, $shift) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_settings WHERE work_date = ? AND shift = ?");
        $stmt->execute([$date, $shift]);
        return $stmt->fetch() ?: null;
    }

    public function findSettingById($id) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_settings WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function createSetting($date, $shift, $actor) {
        $stmt = $this->db->prepare("INSERT INTO v2_mp_settings (work_date, shift, created_by, updated_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$date, $shift, $actor, $actor]);
        return (int)$this->db->lastInsertId();
    }

    public function setStatus($id, $status, $actor) {
        $stmt = $this->db->prepare("UPDATE v2_mp_settings SET status = ?, updated_by = ? WHERE id = ?");
        $stmt->execute([$status, $actor, (int)$id]);
    }

    public function touchSetting($id, $actor, $synced = false) {
        $sql = "UPDATE v2_mp_settings SET updated_by = ?" . ($synced ? ", synced_at = NOW()" : "") . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$actor, (int)$id]);
    }

    public function deleteSetting($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_settings WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    // ----------------------------------------------------------- Penempatan

    public function assignments($settingId) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_assignments WHERE setting_id = ?");
        $stmt->execute([(int)$settingId]);
        return $stmt->fetchAll();
    }

    public function findAssignmentByUnit($settingId, $unitId) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_assignments WHERE setting_id = ? AND unit_id = ?");
        $stmt->execute([(int)$settingId, (int)$unitId]);
        return $stmt->fetch() ?: null;
    }

    public function findAssignmentByNrp($settingId, $nrp) {
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_assignments WHERE setting_id = ? AND nrp = ?");
        $stmt->execute([(int)$settingId, $nrp]);
        return $stmt->fetch() ?: null;
    }

    public function addAssignment($settingId, $unitId, array $p, $source, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_mp_assignments (setting_id, unit_id, nrp, employee_name, phone, home_unit, source, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$settingId, (int)$unitId, $p['nrp'], $p['name'] ?? null, $p['phone'] ?? null,
            $p['unit'] ?? null, $source, $actor,
        ]);
    }

    public function deleteAssignment($id) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_assignments WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    /** Hapus semua penempatan hasil tarik roster (source = roster); penempatan manual tetap. */
    public function deleteRosterAssignments($settingId) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_assignments WHERE setting_id = ? AND source = 'roster'");
        $stmt->execute([(int)$settingId]);
    }

    public function refreshAssignmentSnapshot($id, array $p) {
        $stmt = $this->db->prepare("UPDATE v2_mp_assignments SET employee_name = ?, phone = ?, home_unit = ? WHERE id = ?");
        $stmt->execute([$p['name'] ?? null, $p['phone'] ?? null, $p['unit'] ?? null, (int)$id]);
    }

    // ------------------------------------------------------------ Lain-lain

    private $lainReady = null;

    /** Tabel v2_mp_lain dibuat lewat migrate_manpower_v201_lain.php; sebelum itu fitur Lain-lain manual dianggap kosong. */
    public function lainTableReady() {
        if ($this->lainReady === null) {
            try {
                $stmt = $this->db->query(
                    "SELECT COUNT(*) FROM information_schema.TABLES
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'v2_mp_lain'"
                );
                $this->lainReady = (int)$stmt->fetchColumn() === 1;
            } catch (Throwable $e) {
                $this->lainReady = false;
            }
        }
        return $this->lainReady;
    }

    /** Semua baris Lain-lain (manual + hidden) pada satu setting. */
    public function lainRows($settingId) {
        if (!$this->lainTableReady()) return [];
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_lain WHERE setting_id = ? ORDER BY label, employee_name");
        $stmt->execute([(int)$settingId]);
        return $stmt->fetchAll();
    }

    public function findLain($settingId, $nrp) {
        if (!$this->lainTableReady()) return null;
        $stmt = $this->db->prepare("SELECT * FROM v2_mp_lain WHERE setting_id = ? AND nrp = ?");
        $stmt->execute([(int)$settingId, $nrp]);
        return $stmt->fetch() ?: null;
    }

    /** Pindahkan orang ke Lain-lain secara manual (atau ubah keterangannya). */
    public function upsertLainManual($settingId, array $p, $label, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_mp_lain (setting_id, nrp, employee_name, phone, label, source, created_by)
             VALUES (?, ?, ?, ?, ?, 'manual', ?)
             ON DUPLICATE KEY UPDATE employee_name = VALUES(employee_name), phone = VALUES(phone),
                                     label = VALUES(label), source = 'manual'"
        );
        $stmt->execute([(int)$settingId, $p['nrp'], $p['name'] ?? null, $p['phone'] ?? null, $label, $actor]);
    }

    /** Sembunyikan baris Lain-lain hasil roster (ID/TR/IDS) pada setting ini. */
    public function hideLain($settingId, $nrp, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO v2_mp_lain (setting_id, nrp, source, created_by) VALUES (?, ?, 'hidden', ?)
             ON DUPLICATE KEY UPDATE source = 'hidden', label = NULL"
        );
        $stmt->execute([(int)$settingId, $nrp, $actor]);
    }

    public function deleteLain($settingId, $nrp) {
        if (!$this->lainTableReady()) return;
        $stmt = $this->db->prepare("DELETE FROM v2_mp_lain WHERE setting_id = ? AND nrp = ?");
        $stmt->execute([(int)$settingId, $nrp]);
    }

    // ------------------------------------------------------------- Pelepasan

    /** @return string[] daftar NRP yang dilepas pada setting ini. */
    public function releasedNrps($settingId) {
        $stmt = $this->db->prepare("SELECT nrp FROM v2_mp_released WHERE setting_id = ?");
        $stmt->execute([(int)$settingId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function addReleased($settingId, $nrp, $actor) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO v2_mp_released (setting_id, nrp, created_by) VALUES (?, ?, ?)");
        $stmt->execute([(int)$settingId, $nrp, $actor]);
    }

    public function removeReleased($settingId, $nrp) {
        $stmt = $this->db->prepare("DELETE FROM v2_mp_released WHERE setting_id = ? AND nrp = ?");
        $stmt->execute([(int)$settingId, $nrp]);
    }

    // ------------------------------------------------------- Password CP

    /** Password Check Point (Input PW Speak Up CP, shift D=1 / N=2). Tabel belum ada / tidak terbaca -> kosong. */
    public function checkPointPassword($date, $shiftCode) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM pw_speakup_cp WHERE tanggal = ? AND shift = ? LIMIT 1");
            $stmt->execute([$date, $shiftCode === 'D' ? '1' : '2']);
            return trim((string)$stmt->fetchColumn());
        } catch (Throwable $e) {
            return '';
        }
    }
}
