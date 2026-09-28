<?php

namespace App\Services;

use App\Models\RosterMonthlyMaster;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetRosterImporter
{
    private const MONTH_MAP = [
        'jan' => 1, 'januari' => 1, 'january' => 1,
        'feb' => 2, 'februari' => 2, 'february' => 2,
        'mar' => 3, 'maret' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'mei' => 5, 'may' => 5,
        'jun' => 6, 'juni' => 6, 'june' => 6,
        'jul' => 7, 'juli' => 7, 'july' => 7,
        'agu' => 8, 'agustus' => 8, 'august' => 8, 'aug' => 8,
        'sep' => 9, 'september' => 9,
        'okt' => 10, 'oktober' => 10, 'october' => 10, 'oct' => 10,
        'nov' => 11, 'november' => 11, 'nop' => 11,
        'des' => 12, 'desember' => 12, 'december' => 12, 'dec' => 12,
    ];

    /**
     * Konversi URL Google Spreadsheet ke link direct export CSV
     */
    public function googleSheetUrlToCsv(string $url): ?string
    {
        $id = null;
        $gid = '0';
        if (preg_match('#/d/([a-zA-Z0-9-_]+)#', $url, $m)) {
            $id = $m[1];
        }
        if (preg_match('/[\?#&]gid=(\d+)/', $url, $m)) {
            $gid = $m[1];
        }
        if (! $id) {
            return null;
        }

        return "https://docs.google.com/spreadsheets/d/{$id}/export?format=csv&gid={$gid}";
    }

    /**
     * Ambil konten CSV via HTTP
     */
    public function fetchCsvContent(string $csvUrl): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(45)
                ->get($csvUrl);

            if ($response->successful()) {
                $body = $response->body();
                if (stripos(ltrim($body), '<html') !== false) {
                    return null; // Mengembalikan halaman HTML login Google, berarti bukan publik
                }

                return $body;
            }
        } catch (\Throwable $e) {
            Log::error('fetchCsvContent error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Parse konten CSV matriks Google Sheets (identik dengan V1 one-click)
     */
    public function parseRosterMatrix(string $csvContent): array
    {
        // Hapus UTF-8 BOM
        $csvContent = preg_replace('/^\xEF\xBB\xBF/', '', $csvContent);

        $rows = [];
        $fp = fopen('php://memory', 'r+');
        fwrite($fp, $csvContent);
        rewind($fp);
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if ($data === null) {
                continue;
            }
            $rows[] = $data;
        }
        fclose($fp);

        if (empty($rows)) {
            return [];
        }

        $allRecords = [];
        $currentSection = '';
        $columnDates = [];
        $defaultYear = (int) date('Y');

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($row) < 3) {
                continue;
            }

            // Cek baris full date (e.g. 1/1/2026 atau 2026-01-01)
            if ($this->isFullDateRow($row, 1)) {
                $dateFormat = $this->detectDateFormatFromRow($row);
                $newColumnDates = [];
                foreach ($row as $colIdx => $cell) {
                    if ($colIdx < 2) {
                        continue;
                    }
                    $date = $this->detectFullDate($cell, $dateFormat);
                    if ($date !== null) {
                        $newColumnDates[$colIdx] = $date;
                    }
                }
                if (! empty($newColumnDates)) {
                    $columnDates = $newColumnDates;
                    $combinedForSection = strtoupper(trim(implode(' ', array_slice(array_map('trim', $row), 0, 4))));
                    if ($this->matchesSectionKeyword($combinedForSection) && ! $this->isEmployeeRow($row)) {
                        $currentSection = trim(preg_replace('/\s+/', ' ', $combinedForSection));
                    }

                    continue;
                }
            }

            // Cek baris header bulan
            $monthsFound = [];
            foreach ($row as $colIdx => $cell) {
                if ($colIdx < 4) {
                    continue;
                }
                $detected = $this->detectMonthFromCell($cell);
                if ($detected) {
                    $monthsFound[$colIdx] = $detected;
                }
            }

            if (count($monthsFound) >= 1) {
                $combinedForSection = strtoupper(trim(implode(' ', array_slice(array_map('trim', $row), 0, 5))));
                if ($this->matchesSectionKeyword($combinedForSection) && ! $this->isEmployeeRow($row)) {
                    $currentSection = trim(preg_replace('/\s+/', ' ', $combinedForSection));
                }

                for ($j = $i; $j <= min($i + 4, count($rows) - 1); $j++) {
                    if ($this->isDateNumberRow($rows[$j])) {
                        $columnDates = [];
                        ksort($monthsFound);
                        $monthEntries = [];
                        foreach ($monthsFound as $col => $info) {
                            $monthEntries[] = array_merge($info, ['col' => $col]);
                        }
                        $knownYear = $defaultYear;
                        foreach ($monthEntries as &$entry) {
                            if ($entry['year'] !== null) {
                                $knownYear = $entry['year'];
                            }
                            if ($entry['year'] === null) {
                                $entry['year'] = $knownYear;
                            }
                        }
                        unset($entry);

                        for ($k = 1; $k < count($monthEntries); $k++) {
                            if ($monthEntries[$k]['month'] < $monthEntries[$k - 1]['month']) {
                                if ($monthEntries[$k]['year'] <= $monthEntries[$k - 1]['year']) {
                                    $monthEntries[$k]['year'] = $monthEntries[$k - 1]['year'] + 1;
                                }
                            }
                        }

                        foreach ($rows[$j] as $colIdx => $cell) {
                            if ($colIdx < 4) {
                                continue;
                            }
                            $day = trim($cell);
                            if (! is_numeric($day) || (int) $day < 1 || (int) $day > 31) {
                                continue;
                            }
                            $day = (int) $day;
                            $month = null;
                            $year = null;
                            foreach (array_reverse($monthEntries) as $entry) {
                                if ($colIdx >= $entry['col']) {
                                    $month = $entry['month'];
                                    $year = $entry['year'];
                                    break;
                                }
                            }
                            if ($month === null && ! empty($monthEntries)) {
                                $month = $monthEntries[0]['month'];
                                $year = $monthEntries[0]['year'];
                            }
                            if ($month === null) {
                                continue;
                            }
                            if (@checkdate($month, $day, $year)) {
                                $columnDates[$colIdx] = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            }
                        }
                        $i = $j;
                        break;
                    }
                }

                continue;
            }

            $combined = strtoupper(trim(implode(' ', array_slice(array_map('trim', $row), 0, 5))));
            if ($this->matchesSectionKeyword($combined) && ! $this->isEmployeeRow($row)) {
                $currentSection = trim(preg_replace('/\s+/', ' ', $combined));

                continue;
            }

            if ($this->isSkipRow($row)) {
                continue;
            }
            if (! $this->isEmployeeRow($row) || empty($columnDates)) {
                continue;
            }

            $nrpCol = -1;
            for ($c = 0; $c <= 3; $c++) {
                if (isset($row[$c]) && preg_match('/^\d{4,8}$/', trim($row[$c]))) {
                    $nrpCol = $c;
                    break;
                }
            }
            if ($nrpCol === -1) {
                continue;
            }

            $nrp = trim($row[$nrpCol]);
            $nama = '';
            if ($nrpCol > 0 && ! preg_match('/^\d+$/', trim($row[$nrpCol - 1]))) {
                $nama = trim($row[$nrpCol - 1]);
            } else {
                $nama = trim($row[$nrpCol + 1] ?? '');
            }

            $unit = trim($row[$nrpCol + 2] ?? '');
            if (empty($unit) || is_numeric($unit)) {
                $unit = trim($row[$nrpCol + 3] ?? '');
            }

            $jabatan = $currentSection;

            if (empty($nrp) || empty($nama)) {
                continue;
            }

            foreach ($columnDates as $colIdx => $date) {
                $kode = strtoupper(trim($row[$colIdx] ?? ''));
                if ($kode === '' || strpos($kode, '#') !== false) {
                    continue;
                }
                if (strlen($kode) > 20) {
                    continue;
                }

                $allRecords[] = [
                    'nrp' => $nrp,
                    'nama' => $nama,
                    'jabatan' => $jabatan,
                    'unit' => $unit,
                    'section' => $currentSection,
                    'tanggal' => $date,
                    'kode' => $kode,
                ];
            }
        }

        return $allRecords;
    }

    /**
     * Batch insert / upsert ke roster_monthly_master
     */
    public function importMonthlyBatch(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $chunkSize = 500;
        $count = 0;

        foreach (array_chunk($records, $chunkSize) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?,?)'));
            $sql = "INSERT INTO roster_monthly_master (nrp, tanggal, kode, unit)
                    VALUES {$placeholders}
                    ON DUPLICATE KEY UPDATE
                        kode = VALUES(kode),
                        unit = VALUES(unit)";

            $params = [];
            foreach ($chunk as $r) {
                $params[] = (string) $r['nrp'];
                $params[] = (string) $r['tanggal'];
                $params[] = (string) $r['kode'];
                $params[] = $r['unit'] !== null ? (string) $r['unit'] : null;
            }

            DB::statement($sql, $params);
            $count += count($chunk);
        }

        // Bulk update jabatan_roster pada tabel employees
        // Kelompokkan per roster group dan pastikan NRP diperlakukan sebagai string murni
        $groupsByRoster = [];
        foreach ($records as $b) {
            $rg = trim((string) ($b['roster_group'] ?? ''));
            $nrp = trim((string) ($b['nrp'] ?? ''));
            if ($rg !== '' && $nrp !== '') {
                $groupsByRoster[$rg][$nrp] = true;
            }
        }

        foreach ($groupsByRoster as $rosterGroup => $nrpMap) {
            $nrpList = array_map('strval', array_keys($nrpMap));
            foreach (array_chunk($nrpList, 300) as $nrpChunk) {
                $quotedNrp = implode(',', array_map(fn($n) => "'" . addslashes((string) $n) . "'", $nrpChunk));
                DB::statement("UPDATE employees SET jabatan_roster = ? WHERE nrp IN ({$quotedNrp})", [(string) $rosterGroup]);
            }
        }

        return $count;
    }

    /**
     * Import dari Google Sheets URL
     */
    public function importFromUrl(string $sheetUrl, string $groupLabel): array
    {
        $csvUrl = $this->googleSheetUrlToCsv($sheetUrl);
        if (! $csvUrl) {
            return ['success' => false, 'error' => 'Format URL Google Spreadsheet tidak valid.'];
        }

        $csvContent = $this->fetchCsvContent($csvUrl);
        if ($csvContent === null || trim($csvContent) === '') {
            return [
                'success' => false,
                'error' => 'Gagal mengambil data dari Google Sheets. Pastikan spreadsheet sudah di-share "Anyone with the link" (Public).',
            ];
        }

        $records = $this->parseRosterMatrix($csvContent);
        if (empty($records)) {
            return [
                'success' => false,
                'error' => 'Tidak ada baris data roster yang terdeteksi dalam spreadsheet ini. Pastikan ada baris tanggal dan data karyawan.',
            ];
        }

        foreach ($records as &$rec) {
            $rec['roster_group'] = $groupLabel;
            if (empty($rec['section'])) {
                $rec['section'] = $groupLabel;
            }
        }
        unset($rec);

        $count = $this->importMonthlyBatch($records);

        return [
            'success' => true,
            'count' => $count,
            'message' => "Berhasil mengimpor {$count} data roster untuk {$groupLabel}.",
        ];
    }

    /**
     * Import dari file Excel / CSV lokal
     */
    public function importFromFile(UploadedFile $file, ?string $groupLabel = null): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'csv') {
            $content = file_get_contents($file->getRealPath());
            $records = $this->parseRosterMatrix($content);
        } else {
            // Excel (.xlsx/.xls) via PhpSpreadsheet
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();

            // Convert worksheet to CSV string in memory
            $fp = fopen('php://memory', 'r+');
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = (string) $cell->getValue();
                }
                fputcsv($fp, $cells);
            }
            rewind($fp);
            $content = stream_get_contents($fp);
            fclose($fp);

            $records = $this->parseRosterMatrix($content);
        }

        if (empty($records)) {
            return ['success' => false, 'error' => 'Tidak ada format roster yang terdeteksi dari file.'];
        }

        if ($groupLabel) {
            foreach ($records as &$rec) {
                $rec['roster_group'] = $groupLabel;
            }
            unset($rec);
        }

        $count = $this->importMonthlyBatch($records);

        return [
            'success' => true,
            'count' => $count,
            'message' => "Berhasil mengimpor {$count} data roster dari file.",
        ];
    }

    // ================= HELPER METHODS =================

    private function matchesSectionKeyword(string $text): bool
    {
        $keywords = [
            'ADMIN', 'ICC', 'HELPER', 'OPERATOR', 'PRODUKSI', 'SERVICE',
            'ODP', 'MANPOWER', 'GROUP', 'FRONT', 'COAL', 'DISPOSAL',
            'DEWATERING', 'HRM', 'INFRAS', 'PLANT', 'GL', 'GROUP LEADER',
            'FOREMAN', 'SUPERVISOR',
        ];

        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) {
                return true;
            }
        }

        return false;
    }

    private function detectMonthFromCell(string $cell): ?array
    {
        $cell = strtolower(trim($cell));
        if (empty($cell)) {
            return null;
        }

        $month = null;
        $year = null;

        $words = preg_split('/[\s,.\-\/]+/', $cell);
        foreach ($words as $word) {
            if (isset(self::MONTH_MAP[$word])) {
                $month = self::MONTH_MAP[$word];
            }
            if (preg_match('/^\d{4}$/', $word)) {
                $year = (int) $word;
            }
        }

        if ($month === null) {
            return null;
        }

        return ['month' => $month, 'year' => $year ?? (int) date('Y')];
    }

    private function detectDateFormatFromRow(array $row, int $minCol = 2): string
    {
        $cells = [];
        foreach ($row as $colIdx => $cell) {
            if ($colIdx < $minCol) {
                continue;
            }
            $cell = trim($cell);
            if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $cell, $m)) {
                $cells[] = ['col' => $colIdx, 'p1' => (int) $m[1], 'p2' => (int) $m[2]];
            }
        }
        if (count($cells) < 3) {
            return 'dmy';
        }

        $p1_increments = 0;
        $p2_increments = 0;
        $p1_same = 0;
        $p2_same = 0;

        for ($i = 1; $i < count($cells); $i++) {
            if ($cells[$i]['p1'] == $cells[$i - 1]['p1']) {
                $p1_same++;
            }
            if ($cells[$i]['p2'] == $cells[$i - 1]['p2']) {
                $p2_same++;
            }
            if ($cells[$i]['p1'] == $cells[$i - 1]['p1'] + 1) {
                $p1_increments++;
            }
            if ($cells[$i]['p2'] == $cells[$i - 1]['p2'] + 1) {
                $p2_increments++;
            }
        }

        if ($p2_increments > $p1_increments && $p1_same > $p2_same) {
            return 'mdy';
        }

        return 'dmy';
    }

    private function detectFullDate(string $cell, string $format = 'dmy'): ?string
    {
        $cell = trim($cell);
        if (empty($cell)) {
            return null;
        }

        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $cell, $m)) {
            if ($format === 'mdy') {
                $month = (int) $m[1];
                $day = (int) $m[2];
            } else {
                $day = (int) $m[1];
                $month = (int) $m[2];
            }
            $year = (int) $m[3];

            if (@checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            // Fallback swap
            [$month, $day] = [$day, $month];
            if (@checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('#^(\d{4})[/\-](\d{1,2})[/\-](\d{1,2})$#', $cell, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if (@checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        return null;
    }

    private function isFullDateRow(array $row, int $minCol = 2): bool
    {
        $format = $this->detectDateFormatFromRow($row, $minCol);
        $dateCount = 0;
        foreach ($row as $colIdx => $cell) {
            if ($colIdx < $minCol) {
                continue;
            }
            if ($this->detectFullDate($cell, $format) !== null) {
                $dateCount++;
            }
        }

        return $dateCount >= 3;
    }

    private function isDateNumberRow(array $row, int $minCol = 1): bool
    {
        $numericCount = 0;
        foreach ($row as $colIdx => $cell) {
            if ($colIdx < $minCol) {
                continue;
            }
            $val = trim($cell);
            if ($val !== '' && is_numeric($val) && (int) $val >= 1 && (int) $val <= 31) {
                $numericCount++;
            }
        }

        return $numericCount >= 5;
    }

    private function isEmployeeRow(array $row): bool
    {
        for ($i = 0; $i <= 3; $i++) {
            if (isset($row[$i]) && preg_match('/^\d{4,8}$/', trim($row[$i]))) {
                return true;
            }
        }

        return false;
    }

    private function isSkipRow(array $row): bool
    {
        for ($i = 0; $i <= 3; $i++) {
            if (isset($row[$i]) && preg_match('/^\d{4,8}$/', trim($row[$i]))) {
                return false;
            }
        }
        $first = strtolower(trim($row[0] ?? ''));
        $skipWords = ['no', 'siang', 'malam', 'off', 'cuti', 'induksi', 'mcu', 'training', 'total'];

        return $first === '' || in_array($first, $skipWords);
    }
}
