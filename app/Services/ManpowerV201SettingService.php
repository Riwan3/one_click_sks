<?php

namespace App\Services;

use App\Exceptions\ManpowerV201Conflict;
use App\Exceptions\ManpowerV201NotFound;
use App\Repositories\ManpowerV201Repository;
use App\Repositories\ManpowerV201SettingRepository;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

//
// Setting Harian Manpower v2.01 (per tanggal + shift), sumber hadir = Roster V2. Port One-Click V1.
//
// Klasifikasi orang yang hadir (kode D/N sesuai shift) menurut kolom unit di roster:
//   gl       jabatan mengandung GROUP LEADER -> kotak GL (tidak dipasang ke unit)
//   batangan unit-nya terdaftar di master CSA  -> otomatis dipasang ke unit-nya
//   master   unit diawali "MASTER"             -> daftar sisa, diplot manual
//   unknown  unit berisi angka tapi belum ada di master CSA -> daftar sisa + peringatan
//   other    helper/admin/ICC dll. (tanpa angka) -> diabaikan
// Dua batangan hadir untuk satu unit = DOUBLE: tidak dipasang otomatis, admin memilih salah satu.
// Yang dilepas / tidak kebagian unit masuk daftar sisa.
// Kotak LAIN-LAIN: orang berstatus ID/TR/IDS di Roster V2 pada tanggal itu (bukan D/N), lihat otherStatus().
//
class ManpowerV201SettingService {
    /** Keterangan kotak LAIN-LAIN untuk status roster. */
    const LAIN_LABEL = ['ID' => 'INDUKSI', 'TR' => 'TRAINING', 'IDS' => 'INDUKSI PERMIT'];

    const MONITORING_PAGE_DAYS = 7;

    const MONITORING_STATUSES = ['final', 'draft', 'belum', 'mendatang'];

    private $repo;     // ManpowerV201SettingRepository
    private $master;   // ManpowerV201Repository

    public function __construct(ManpowerV201SettingRepository $repo, ManpowerV201Repository $master) {
        $this->repo = $repo;
        $this->master = $master;
    }

    private function actor() {
        return substr((string) Auth::user()?->username, 0, 50);
    }

    public function tablesReady() {
        return $this->master->tablesReady() && $this->repo->tablesReady();
    }

    // ---------------------------------------------------------------- Input

    public static function validDate($d) {
        return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false
            && date('Y-m-d', strtotime($d)) === $d;
    }

    public static function validShift($s) {
        return $s === 'D' || $s === 'N';
    }

    public static function validMonth($m) {
        return is_string($m) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m) === 1;
    }

    private function checkKey($date, $shift) {
        if (!self::validDate($date)) throw new InvalidArgumentException('Tanggal tidak valid.');
        if (!self::validShift($shift)) throw new InvalidArgumentException('Shift harus D (siang) atau N (malam).');
    }

    private function settingOrFail($id) {
        $s = $this->repo->findSettingById($id);
        if (!$s) throw new ManpowerV201NotFound('Setting tidak ditemukan.');
        return $s;
    }

    private function assertDraft(array $setting) {
        if ($setting['status'] === 'final') {
            throw new ManpowerV201Conflict('Setting sudah difinalkan. Buka final dulu untuk mengubahnya.');
        }
    }

    private function assertLainReady() {
        if (!$this->repo->lainTableReady()) {
            throw new ManpowerV201Conflict('Tabel Lain-lain belum diinisialisasi. Jalankan php artisan migrate.');
        }
    }

    // ------------------------------------------------------- Klasifikasi

    private function unitIndex() {
        $idx = [];
        foreach ($this->master->tree() as $csa) {
            foreach ($csa['categories'] as $cat) {
                foreach ($cat['units'] as $u) {
                    $idx[ManpowerV201Service::normalizeUnit($u['unit'])] = [
                        'id' => (int)$u['id'], 'unit' => $u['unit'],
                        'is_spare_slot' => (int)$u['is_spare_slot'], 'is_high_risk' => (int)$u['is_high_risk'],
                        'category_id' => (int)$cat['id'], 'category' => $cat['name'],
                        'csa_id' => (int)$csa['id'], 'csa' => $csa['name'],
                    ];
                }
            }
        }
        return $idx;
    }

    private function classify(array $p, array $unitIdx) {
        if (preg_match('/GROUP\s+LEADER/i', (string)$p['jabatan'])) return 'gl';
        $u = ManpowerV201Service::normalizeUnit($p['unit']);
        if ($u !== '' && isset($unitIdx[$u]) && !$unitIdx[$u]['is_spare_slot']) return 'batangan';
        if (preg_match('/^MASTER\b/', $u)) return 'master';
        if ($u !== '' && preg_match('/\d/', $u)) return 'unknown';
        return 'other';
    }

    /**
     * Apakah jenis alat asal orang ($homeLabel: nama master atau kategori unit asalnya)
     * cocok dengan kategori tujuan. Dicocokkan lewat angka model (785, 777, 1250, ...).
     * Tanpa angka: pakai kata kunci; bila tidak ada aturan -> dianggap cocok (tidak diberi warning).
     */
    public static function compatible($homeLabel, $categoryName) {
        $nums = function ($s) {
            preg_match_all('/\d{2,4}/', (string)$s, $m);
            $n = array_map('intval', $m[0]);
            foreach ($n as $i => $v) if ($v === 6015) $n[$i] = 1250; // EX 6015 satu kelompok dengan 1250
            return array_unique($n);
        };
        $a = $nums($homeLabel);
        $b = $nums($categoryName);
        if ($a) return (bool)array_intersect($a, $b);

        $home = strtoupper((string)$homeLabel);
        $cat  = strtoupper((string)$categoryName);
        // Awalan nama kategori (bukan substring) agar "DUMP TRUCK" tidak cocok dengan "HEAVY DUMP TRUCK".
        $rules = ['/\bDT\b/' => '/^DUMP TRUCK/', '/\bWT\b/' => '/^WATER TRUCK/', '/\bWL\b/' => '/^WHEEL LOADER/', '/\bVS\b/' => '/^VIBRO/'];
        foreach ($rules as $homeRe => $catRe) {
            if (preg_match($homeRe, $home)) return (bool)preg_match($catRe, $cat);
        }
        return true;
    }

    /**
     * Orang berstatus ID/TR/IDS (roster) yang dihapus admin dari Lain-lain pada setting ini. Artinya mereka sebenarnya
     * masuk kerja: dimasukkan ke Daftar Sisa dan boleh diplot / dipindah. Bentuk baris sama dengan attendance().
     */
    private function reinstated($date, array $hiddenNrps, array $pool) {
        $out = [];
        if (!$hiddenNrps) return $out;
        foreach ($this->repo->otherStatus($date) as $o) {
            $n = (string)$o['nrp'];
            if (!isset($hiddenNrps[$n]) || isset($pool[$n])) continue;
            $out[$n] = ['nrp' => $n, 'name' => $o['name'], 'unit' => $o['unit'], 'section' => '',
                        'phone' => $o['phone'], 'jabatan' => $o['jabatan']];
        }
        return $out;
    }

    /** Hadir D/N + yang dikembalikan dari Lain-lain (untuk validasi plot / pindah). */
    private function effectivePool(array $setting) {
        $pool = $this->repo->attendance($setting['work_date'], $setting['shift']);
        $hidden = [];
        foreach ($this->repo->lainRows((int)$setting['id']) as $lr) {
            $hidden[(string)$lr['nrp']] = true;   // hidden atau manual; reinstated() hanya mengambil yang berstatus ID/TR/IDS di roster
        }
        return $pool + $this->reinstated($setting['work_date'], $hidden, $pool);
    }

    // ----------------------------------------------------------- Tarik data

    /** Buat setting baru lalu tarik dari Roster V2. */
    public function create($date, $shift) {
        $this->checkKey($date, $shift);
        if ($this->repo->findSetting($date, $shift)) {
            throw new ManpowerV201Conflict('Setting untuk tanggal dan shift ini sudah ada.');
        }
        if (!$this->repo->attendance($date, $shift)) {
            throw new ManpowerV201Conflict('Tidak ada yang hadir (kode ' . $shift . ') di Roster V2 pada ' . $date . ' dari roster berstatus published. Pastikan rosternya sudah dipublish di Roster V2.');
        }
        return $this->repo->transaction(function () use ($date, $shift) {
            $id = $this->repo->createSetting($date, $shift, $this->actor());
            $this->syncInternal($this->repo->findSettingById($id));
            return $id;
        });
    }

    /** Tarik ulang dari Roster V2: penempatan manual & pelepasan dipertahankan. */
    public function sync($settingId) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        return $this->repo->transaction(function () use ($setting) {
            return $this->syncInternal($setting);
        });
    }

    private function syncInternal(array $setting) {
        $sid = (int)$setting['id'];
        $pool = $this->repo->attendance($setting['work_date'], $setting['shift']);
        $unitIdx = $this->unitIndex();
        $released = array_flip($this->repo->releasedNrps($sid));

        $this->repo->deleteRosterAssignments($sid);

        // Penempatan manual yang tersisa: perbarui snapshot nama/telepon bila orangnya masih hadir.
        $takenNrp = [];
        $takenUnit = [];
        foreach ($this->repo->assignments($sid) as $a) {
            $takenNrp[$a['nrp']] = true;
            $takenUnit[(int)$a['unit_id']] = true;
            if (isset($pool[$a['nrp']])) $this->repo->refreshAssignmentSnapshot($a['id'], $pool[$a['nrp']]);
        }

        $cands = [];
        foreach ($pool as $nrp => $p) {
            if ($this->classify($p, $unitIdx) !== 'batangan') continue;
            if (isset($released[$nrp]) || isset($takenNrp[$nrp])) continue;
            $cands[ManpowerV201Service::normalizeUnit($p['unit'])][] = $p;
        }

        $placed = 0;
        $doubles = 0;
        foreach ($cands as $code => $list) {
            $unitId = $unitIdx[$code]['id'];
            if (isset($takenUnit[$unitId])) continue;
            if (count($list) > 1) { $doubles++; continue; }
            $this->repo->addAssignment($sid, $unitId, $list[0], 'roster', $this->actor());
            $placed++;
        }
        $this->repo->touchSetting($sid, $this->actor(), true);
        return ['placed' => $placed, 'doubles' => $doubles, 'hadir' => count($pool)];
    }

    // ------------------------------------------------------------ Tampilan

    /**
     * Model data halaman Setting Harian. Bila setting belum ada: hanya jumlah hadir.
     * Return: setting, hadir, tree (CSA->kategori->unit, tiap unit + assignment/doubles), sisa, gl, lain, summary, warnings.
     */
    public function view($date, $shift) {
        $this->checkKey($date, $shift);
        $setting = $this->repo->findSetting($date, $shift);
        $pool = $this->repo->attendance($date, $shift);
        if (!$setting) {
            return ['setting' => null, 'hadir' => count($pool), 'tree' => [], 'sisa' => [], 'gl' => [], 'lain' => [],
                    'summary' => null, 'warnings' => []];
        }

        $sid = (int)$setting['id'];
        $tree = $this->master->tree();
        $unitIdx = $this->unitIndex();
        $released = array_flip($this->repo->releasedNrps($sid));

        $lainManual = [];
        $lainHidden = [];
        foreach ($this->repo->lainRows($sid) as $lr) {
            if ($lr['source'] === 'hidden') $lainHidden[$lr['nrp']] = true; else $lainManual[$lr['nrp']] = $lr;
        }
        // ID/TR/IDS yang dikembalikan (hidden) atau sudah dipindah manual: dianggap masuk kerja.
        $reinst = $this->reinstated($date, $lainHidden + $lainManual, $pool);

        $byUnit = [];
        $assignedNrp = [];
        foreach ($this->repo->assignments($sid) as $a) {
            $byUnit[(int)$a['unit_id']] = $a;
            $assignedNrp[$a['nrp']] = true;
        }

        // Kandidat batangan per unit yang belum terpasang (untuk deteksi double).
        $cands = [];
        foreach ($pool as $nrp => $p) {
            if (isset($assignedNrp[$nrp]) || isset($released[$nrp])) continue;
            if ($this->classify($p, $unitIdx) !== 'batangan') continue;
            $cands[ManpowerV201Service::normalizeUnit($p['unit'])][] = $p;
        }

        $warnings = [];
        $nUnit = $nFilled = $nEmpty = $nDouble = 0;
        $inDouble = [];
        foreach ($tree as &$csa) {
            foreach ($csa['categories'] as &$cat) {
                foreach ($cat['units'] as &$u) {
                    $code = ManpowerV201Service::normalizeUnit($u['unit']);
                    $isSlot = (int)$u['is_spare_slot'] === 1;
                    $a = $byUnit[(int)$u['id']] ?? null;
                    $u['assignment'] = null;
                    $u['double'] = [];
                    if ($a) {
                        $home = ManpowerV201Service::normalizeUnit((string)$a['home_unit']);
                        $a['absent'] = !isset($pool[$a['nrp']]) && !isset($reinst[$a['nrp']]);
                        $a['spare'] = !$isSlot && $home !== $code;
                        $u['assignment'] = $a;
                        if ($a['absent']) $warnings[] = $a['employee_name'] . ' (' . $a['nrp'] . ') di ' . $u['unit'] . ' tidak hadir ' . $setting['shift'] . ' menurut Roster V2 saat ini.';
                    } elseif (!$isSlot && isset($cands[$code]) && count($cands[$code]) > 1) {
                        $u['double'] = $cands[$code];
                        foreach ($cands[$code] as $c) $inDouble[$c['nrp']] = true;
                    }
                    if ($isSlot) continue;
                    $nUnit++;
                    $nFilled += $a ? 1 : 0;
                    $nDouble += (!$a && $u['double']) ? 1 : 0;
                    $nEmpty += (!$a && !$u['double']) ? 1 : 0;
                }
                unset($u);
            }
            unset($cat);
        }
        unset($csa);

        $sisa = [];
        $gl = [];
        foreach ($pool + $reinst as $nrp => $p) {
            $kind = $this->classify($p, $unitIdx);
            if ($kind === 'gl') { $gl[] = $p; continue; }
            if ($kind === 'other' || isset($assignedNrp[$nrp]) || isset($inDouble[$nrp]) || isset($lainManual[$nrp])) continue;
            $code = ManpowerV201Service::normalizeUnit($p['unit']);
            $p['kind'] = $kind;
            $p['home_label'] = ($kind === 'batangan') ? $unitIdx[$code]['category'] : $code;
            $p['released'] = isset($released[$nrp]);
            $sisa[] = $p;
            if ($kind === 'unknown') $warnings[] = $p['name'] . ' (' . $p['nrp'] . ') berunit ' . $code . ' yang belum ada di Master CSA.';
        }
        usort($sisa, fn ($x, $y) => [$x['kind'], $x['home_label'], $x['name']] <=> [$y['kind'], $y['home_label'], $y['name']]);
        usort($gl, fn ($x, $y) => strcmp($x['unit'] . $x['name'], $y['unit'] . $y['name']));

        return [
            'setting' => $setting, 'hadir' => count($pool), 'tree' => $tree, 'sisa' => $sisa, 'gl' => $gl,
            'lain' => $this->buildLain($date, $pool + $reinst, $lainManual, $lainHidden),
            'summary' => ['units' => $nUnit, 'filled' => $nFilled, 'empty' => $nEmpty, 'double' => $nDouble, 'sisa' => count($sisa)],
            'warnings' => $warnings,
        ];
    }

    /**
     * Isi kotak LAIN-LAIN: (a) status ID/TR/IDS dari roster, kecuali yang disembunyikan admin pada setting ini,
     * (b) orang yang dipindahkan manual dari Daftar Sisa. Field: nrp, name, phone, label, source (roster|manual), absent.
     */
    private function buildLain($date, array $pool, array $manual, array $hidden) {
        $out = [];
        foreach ($this->repo->otherStatus($date) as $o) {
            if (isset($hidden[$o['nrp']]) || isset($manual[$o['nrp']])) continue;
            $out[] = ['nrp' => $o['nrp'], 'name' => $o['name'], 'phone' => $o['phone'],
                      'label' => self::LAIN_LABEL[$o['code']] ?? $o['code'], 'source' => 'roster', 'absent' => false];
        }
        $man = [];
        foreach ($manual as $m) {
            $man[] = ['nrp' => (string)$m['nrp'], 'name' => (string)$m['employee_name'], 'phone' => (string)$m['phone'],
                      'label' => (string)$m['label'], 'source' => 'manual', 'absent' => !isset($pool[$m['nrp']])];
        }
        usort($man, fn ($x, $y) => [$x['label'], $x['name']] <=> [$y['label'], $y['name']]);
        return array_merge($out, $man);
    }

    /** View untuk Monitoring Settingan Manpower v2: view() + kode roster & SS6 untuk NRP yang tersetting. */
    public function viewWithCodes($date, $shift) {
        $view = $this->view($date, $shift);
        $nrps = [];
        foreach ($view['tree'] as $csa) foreach ($csa['categories'] as $cat) foreach ($cat['units'] as $u) {
            if ($u['assignment']) $nrps[] = $u['assignment']['nrp'];
        }
        $view['codes'] = $view['setting'] ? $this->statusCodes($date, $nrps) : ['roster' => [], 'ss6' => []];
        return $view;
    }

    /** Password Check Point huruf besar untuk header Preview; null = belum diinput. */
    public function checkPointPassword($date, $shift) {
        $password = $this->repo->checkPointPassword($date, $shift);
        return $password === '' ? null : mb_strtoupper($password);
    }

    /** Kode roster dan kode SS6 pada tanggal setting untuk NRP yang tersetting (kolom Roster & SS6 di monitoring). */
    public function statusCodes($date, array $nrps) {
        return ['roster' => $this->repo->rosterCodes($date, $nrps), 'ss6' => $this->repo->ss6Codes($date, $nrps)];
    }

    // ------------------------------------------------------------ Aksi plot

    /** Plot orang dari daftar sisa ke unit/slot. Ditolak bila unit sudah terisi atau orang sudah di unit lain. */
    public function assign($settingId, $unitId, $nrp) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        $nrp = trim((string)$nrp);
        if ($nrp === '') throw new InvalidArgumentException('NRP wajib diisi.');

        $unit = $this->master->findUnit($unitId);
        if (!$unit) throw new ManpowerV201NotFound('Unit tidak ditemukan.');

        $pool = $this->effectivePool($setting);
        if (!isset($pool[$nrp])) {
            throw new ManpowerV201Conflict('NRP ' . $nrp . ' tidak hadir (kode ' . $setting['shift'] . ') di Roster V2 pada tanggal ini.');
        }
        if ($this->classify($pool[$nrp], $this->unitIndex()) === 'gl') {
            throw new ManpowerV201Conflict('Group Leader tidak diplot ke unit; mereka tampil di kotak GL.');
        }

        $sid = (int)$setting['id'];
        $this->repo->transaction(function () use ($sid, $unit, $nrp, $pool) {
            $curUnit = $this->repo->findAssignmentByUnit($sid, $unit['id']);
            if ($curUnit) {
                throw new ManpowerV201Conflict('Unit ' . $unit['unit'] . ' sudah terisi ' . $curUnit['employee_name'] . '. Lepas dulu sebelum mengisi ulang.');
            }
            $inLain = $this->repo->findLain($sid, $nrp);
            if ($inLain && $inLain['source'] === 'manual') {
                throw new ManpowerV201Conflict($pool[$nrp]['name'] . ' sedang berada di Lain-lain. Kembalikan ke daftar sisa dulu (hapus dari Lain-lain).');
            }
            if ($this->repo->findAssignmentByNrp($sid, $nrp)) {
                throw new ManpowerV201Conflict($pool[$nrp]['name'] . ' sudah ditempatkan di unit lain. Lepas dulu dari unit tersebut.');
            }
            $this->repo->addAssignment($sid, $unit['id'], $pool[$nrp], 'manual', $this->actor());
            $this->repo->removeReleased($sid, $nrp);
            $this->repo->touchSetting($sid, $this->actor());
        });
    }

    /** Lepas orang dari unit (menjadi daftar sisa). Batangan hasil roster dicatat dilepas agar tarik ulang tidak memasangnya lagi. */
    public function unassign($settingId, $unitId) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        $sid = (int)$setting['id'];
        $this->repo->transaction(function () use ($sid, $unitId) {
            $a = $this->repo->findAssignmentByUnit($sid, $unitId);
            if (!$a) throw new ManpowerV201NotFound('Unit ini belum terisi.');
            $this->repo->deleteAssignment($a['id']);
            if ($a['source'] === 'roster') $this->repo->addReleased($sid, $a['nrp'], $this->actor());
            $this->repo->touchSetting($sid, $this->actor());
        });
    }

    /** Selesaikan double: pasang $nrp ke unit, batangan lain untuk unit itu dilepas. */
    public function resolveDouble($settingId, $unitId, $nrp) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        $sid = (int)$setting['id'];
        $unit = $this->master->findUnit($unitId);
        if (!$unit) throw new ManpowerV201NotFound('Unit tidak ditemukan.');

        $nrp = (string)$nrp;
        $pool = $this->repo->attendance($setting['work_date'], $setting['shift']);
        $unitIdx = $this->unitIndex();
        $code = ManpowerV201Service::normalizeUnit($unit['unit']);

        $this->repo->transaction(function () use ($sid, $unit, $nrp, $pool, $unitIdx, $code) {
            if ($this->repo->findAssignmentByUnit($sid, $unit['id'])) {
                throw new ManpowerV201Conflict('Unit ' . $unit['unit'] . ' sudah terisi.');
            }
            $released = array_flip($this->repo->releasedNrps($sid));
            $cands = [];
            foreach ($pool as $n => $p) {
                if (isset($released[$n]) || $this->repo->findAssignmentByNrp($sid, $n)) continue;
                $isUnitBatangan = $this->classify($p, $unitIdx) === 'batangan' && ManpowerV201Service::normalizeUnit($p['unit']) === $code;
                if ($isUnitBatangan) $cands[$n] = $p;
            }
            if (!isset($cands[$nrp])) throw new ManpowerV201Conflict('NRP ' . $nrp . ' bukan batangan unit ini yang hadir.');
            $this->repo->addAssignment($sid, $unit['id'], $cands[$nrp], 'roster', $this->actor());
            foreach (array_keys($cands) as $n) {
                if ((string)$n !== $nrp) $this->repo->addReleased($sid, (string)$n, $this->actor());
            }
            $this->repo->touchSetting($sid, $this->actor());
        });
    }

    /** Pindahkan orang (hadir, belum di unit) dari Daftar Sisa ke kotak LAIN-LAIN dengan keterangan bebas. */
    public function moveToLain($settingId, $nrp, $label) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        $nrp = trim((string)$nrp);
        $label = strtoupper(trim(preg_replace('/\s+/', ' ', (string)$label)));
        if ($nrp === '') throw new InvalidArgumentException('NRP wajib diisi.');
        if ($label === '') throw new InvalidArgumentException('Keterangan Lain-lain wajib diisi.');
        if (mb_strlen($label) > 40) throw new InvalidArgumentException('Keterangan maksimal 40 karakter.');
        $this->assertLainReady();

        $sid = (int)$setting['id'];
        $pool = $this->effectivePool($setting);
        if (!isset($pool[$nrp])) {
            throw new ManpowerV201Conflict('NRP ' . $nrp . ' tidak hadir (kode ' . $setting['shift'] . ') di Roster V2 pada tanggal ini.');
        }
        if ($this->classify($pool[$nrp], $this->unitIndex()) === 'gl') {
            throw new ManpowerV201Conflict('Group Leader tampil di kotak GL, tidak dipindah ke Lain-lain.');
        }
        $this->repo->transaction(function () use ($sid, $nrp, $label, $pool) {
            if ($this->repo->findAssignmentByNrp($sid, $nrp)) {
                throw new ManpowerV201Conflict($pool[$nrp]['name'] . ' sudah ditempatkan di unit. Lepas dulu dari unit tersebut.');
            }
            $this->repo->upsertLainManual($sid, $pool[$nrp], $label, $this->actor());
            $this->repo->touchSetting($sid, $this->actor());
        });
    }

    /**
     * Hapus orang dari Lain-lain. Yang dipindahkan manual kembali ke Daftar Sisa; yang berasal dari roster
     * (ID/TR/IDS) disembunyikan hanya pada setting ini.
     */
    public function removeFromLain($settingId, $nrp) {
        $setting = $this->settingOrFail($settingId);
        $this->assertDraft($setting);
        $nrp = trim((string)$nrp);
        $this->assertLainReady();
        $sid = (int)$setting['id'];
        $this->repo->transaction(function () use ($setting, $sid, $nrp) {
            $row = $this->repo->findLain($sid, $nrp);
            if ($row && $row['source'] !== 'manual') throw new ManpowerV201NotFound('Orang ini sudah dihapus dari Lain-lain.');
            if ($row) {
                $this->repo->deleteLain($sid, $nrp);
                $this->repo->touchSetting($sid, $this->actor());
                return;
            }
            $isRoster = in_array($nrp, array_column($this->repo->otherStatus($setting['work_date']), 'nrp'), true);
            if (!$isRoster) throw new ManpowerV201NotFound('Orang ini tidak ada di Lain-lain.');
            $this->repo->hideLain($sid, $nrp, $this->actor());
            $this->repo->touchSetting($sid, $this->actor());
        });
    }

    public function finalize($settingId) {
        $setting = $this->settingOrFail($settingId);
        $this->repo->setStatus($setting['id'], 'final', $this->actor());
    }

    public function reopen($settingId) {
        $setting = $this->settingOrFail($settingId);
        $this->repo->setStatus($setting['id'], 'draft', $this->actor());
    }

    /** Daftar kandidat untuk modal Plot: sisa + penanda kecocokan terhadap kategori unit tujuan. */
    public function candidatesFor($date, $shift, $unitId) {
        $unit = $this->master->findUnit($unitId);
        if (!$unit) throw new ManpowerV201NotFound('Unit tidak ditemukan.');
        $cat = $this->master->findCategory($unit['category_id']);
        $out = [];
        foreach ($this->view($date, $shift)['sisa'] as $p) {
            $out[] = [
                'nrp' => $p['nrp'], 'name' => $p['name'], 'kind' => $p['kind'], 'home_label' => $p['home_label'],
                'compatible' => self::compatible($p['home_label'], $cat['name']),
            ];
        }
        return $out;
    }

    // ------------------------------------------------------------ Monitoring

    /**
     * Monitoring per tanggal + shift untuk satu bulan ($month = 'YYYY-MM'), urut kronologis (tanggal 1 di atas),
     * ditampilkan per halaman berisi 7 tanggal (1-7, 8-14, ...).
     * Status: final | draft | belum (belum disetting padahal roster ada, hanya untuk hari ini dan besok)
     *         | mendatang (belum disetting, tanggal setelah besok).
     * Dilewati: tanggal+shift tanpa setting dan tanpa orang hadir di roster; juga hari yang sudah lewat tanpa setting.
     * $statusFilter: '' (semua) | final | draft | belum | mendatang  (berlaku di dalam halaman; ringkasan counts = sebulan penuh).
     * $page: nomor halaman (1..n); null = halaman yang memuat hari ini bila bulan berjalan, selain itu halaman 1.
     * Return: ['month', 'rows' (halaman ini), 'counts' (sebulan), 'pages' => [[no, dari, sampai], ...], 'page'].
     */
    public function monitoring($month, $statusFilter = '', $page = null) {
        if (!self::validMonth($month)) $month = date('Y-m');
        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));
        $lastDay = (int)date('t', strtotime($from));

        [$rows, $counts] = $this->monitoringSkeleton($from, $to);

        $size = self::MONITORING_PAGE_DAYS;
        $totalPages = (int)ceil($lastDay / $size);
        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) $pages[] = [$i, ($i - 1) * $size + 1, min($i * $size, $lastDay)];
        $page = $page ?? ($month === date('Y-m') ? (int)ceil((int)date('j') / $size) : 1);
        $page = max(1, min($totalPages, (int)$page));
        [, $dFrom, $dTo] = $pages[$page - 1];

        $filterByStatus = in_array($statusFilter, self::MONITORING_STATUSES, true);
        $rows = array_values(array_filter($rows, function ($x) use ($dFrom, $dTo, $statusFilter, $filterByStatus) {
            if ($x['day'] < $dFrom || $x['day'] > $dTo) return false;
            return !$filterByStatus || $x['status'] === $statusFilter;
        }));

        // Ringkasan kelengkapan hanya untuk baris yang tampil.
        foreach ($rows as &$x) {
            if (!$x['setting']) continue;
            $view = $this->view($x['date'], $x['shift']);
            $x['summary'] = $view['summary'];
            $x['warnings'] = count($view['warnings']);
        }
        unset($x);

        $names = $this->repo->employeeNames(array_column($rows, 'updated_by'));
        foreach ($rows as &$x) {
            $u = (string)$x['updated_by'];
            $x['updated_by_name'] = $u === '' ? '' : ($names[$u] ?? $u);
        }
        unset($x);

        return ['month' => $month, 'rows' => $rows, 'counts' => $counts, 'pages' => $pages, 'page' => $page];
    }

    /** Rangka baris monitoring sebulan (tanpa ringkasan kelengkapan) + hitungan status sebulan penuh. */
    private function monitoringSkeleton($from, $to) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $hadir = $this->repo->rosterHadirByDate($from, $to);
        $settings = [];
        foreach ($this->repo->settingsBetween($from, $to) as $st) $settings[$st['work_date'] . '|' . $st['shift']] = $st;

        $rows = [];
        $counts = array_fill_keys(self::MONITORING_STATUSES, 0);
        for ($ts = strtotime($from); $ts <= strtotime($to); $ts += 86400) {
            $date = date('Y-m-d', $ts);
            foreach (['D', 'N'] as $shift) {
                $n = $hadir[$date][$shift] ?? 0;
                $st = $settings[$date . '|' . $shift] ?? null;
                if (!$st && $n === 0) continue;
                if (!$st && $date < $today) continue;   // hari yang sudah lewat tanpa setting tidak dipantau

                $status = $st ? ($st['status'] === 'final' ? 'final' : 'draft') : ($date <= $tomorrow ? 'belum' : 'mendatang');
                $counts[$status]++;
                $rows[] = ['date' => $date, 'day' => (int)date('j', $ts), 'shift' => $shift, 'hadir' => $n, 'setting' => $st,
                           'status' => $status, 'summary' => null, 'warnings' => 0,
                           'updated_by' => $st['updated_by'] ?? null, 'updated_at' => $st['updated_at'] ?? null];
            }
        }
        return [$rows, $counts];
    }
}
