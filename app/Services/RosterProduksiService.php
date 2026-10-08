<?php

namespace App\Services;

use App\Repositories\RosterV2Repository;
use Throwable;

/** Data "Monitoring Roster Departement": matriks roster PUBLISHED satu bulan + akumulasi harian & per karyawan. */
class RosterProduksiService
{
    /** Baris akumulasi harian: kode shift => label. */
    public const DAILY_LABELS = [
        'D' => 'SIANG', 'N' => 'MALAM', 'OFF' => 'OFF', 'CT' => 'CUTI', 'ID' => 'INDUKSI',
        'IDS' => 'IDS (PERMIT)', 'MCU' => 'MCU', 'FU' => 'FU', 'H' => 'H', 'TR' => 'TRAINING',
    ];

    /** Kolom akumulasi per karyawan: kode shift => kolom (ID & IDS digabung sebagai induksi). */
    private const EMPLOYEE_COLUMNS = [
        'D' => 'siang', 'N' => 'malam', 'OFF' => 'off', 'CT' => 'cuti', 'ID' => 'induksi',
        'IDS' => 'induksi', 'MCU' => 'mcu', 'FU' => 'fu', 'H' => 'h', 'TR' => 'tr',
    ];

    public function __construct(private RosterV2Repository $repository) {}

    public function build(int $year, int $month, string $category, string $jabatan): array
    {
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $base = [
            'category' => $category, 'categories' => [], 'jabatanList' => [], 'error' => '',
            'days' => $this->dayHeaders($year, $month, $daysInMonth),
            'rows' => [], 'daily' => [], 'dailyTotal' => [], 'employeeTotals' => [], 'grandTotal' => [],
        ];

        try {
            $categories = $this->repository->monitoringGroups($year, $month);
            $category = in_array($category, $categories, true) ? $category : '';
            $jabatanList = $this->repository->monitoringJabatan($year, $month, $category);
            $cells = $this->repository->monitoringCells($year, $month, $category, $jabatan);
        } catch (Throwable $e) {
            report($e);

            return ['error' => 'Data Roster V2 tidak dapat dimuat (tabel belum tersedia atau terjadi kesalahan).'] + $base;
        }

        $rows = $this->pivot($cells, $daysInMonth);

        return ['category' => $category, 'categories' => $categories, 'jabatanList' => $jabatanList, 'rows' => $rows]
            + $this->accumulate($rows, $daysInMonth)
            + $base;
    }

    /** Header tanggal: [{day, dow}] dengan dow 0 = Minggu. */
    private function dayHeaders(int $year, int $month, int $daysInMonth): array
    {
        return array_map(
            fn (int $day) => ['day' => $day, 'dow' => (int) date('w', mktime(0, 0, 0, $month, $day, $year))],
            range(1, $daysInMonth),
        );
    }

    /** Satu baris per NRP (urutan kemunculan); NRP di >1 roster: info baris pertama, sel roster terakhir menimpa. */
    private function pivot(array $cells, int $daysInMonth): array
    {
        $rows = [];
        foreach ($cells as $cell) {
            $nrp = (string) $cell['nrp'];
            $rows[$nrp] ??= [
                'nrp' => $nrp,
                'nama' => $cell['nama'],
                'jabatan' => $cell['jabatan'],
                'grup' => trim($cell['kategori'] . ($cell['section'] !== '' ? ' · ' . $cell['section'] : '')),
                'cells' => array_fill(0, $daysInMonth, ''),
            ];
            $rows[$nrp]['cells'][(int) date('j', strtotime($cell['tanggal'])) - 1] = $cell['kode'];
        }

        return array_values($rows);
    }

    private function accumulate(array $rows, int $daysInMonth): array
    {
        $daily = array_map(fn () => array_fill(0, $daysInMonth, 0), self::DAILY_LABELS);
        $dailyTotal = array_fill(0, $daysInMonth, 0);
        $blank = array_fill_keys([...array_unique(array_values(self::EMPLOYEE_COLUMNS)), 'total'], 0);
        $grand = $blank;
        $employeeTotals = [];

        foreach ($rows as $row) {
            $own = $blank;
            foreach ($row['cells'] as $index => $kode) {
                $kode = strtoupper(trim($kode));
                if ($kode === '') {
                    continue;
                }
                $dailyTotal[$index]++;
                $own['total']++;
                if (isset($daily[$kode])) {
                    $daily[$kode][$index]++;
                }
                if (isset(self::EMPLOYEE_COLUMNS[$kode])) {
                    $own[self::EMPLOYEE_COLUMNS[$kode]]++;
                }
            }
            foreach ($own as $key => $value) {
                $grand[$key] += $value;
            }
            $employeeTotals[] = $own;
        }

        return [
            'daily' => collect(self::DAILY_LABELS)
                ->map(fn (string $label, string $kode) => ['kode' => $kode, 'label' => $label, 'counts' => $daily[$kode]])
                ->values()->all(),
            'dailyTotal' => $dailyTotal,
            'employeeTotals' => $employeeTotals,
            'grandTotal' => $grand,
        ];
    }
}
