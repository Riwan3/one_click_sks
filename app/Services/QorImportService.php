<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\QorAtrAttendance;
use App\Models\QorDpr;
use App\Models\QorSapHm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class QorImportService
{
    public function importDpr(UploadedFile $file, string $source = 'OB'): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $headerIdx = -1;
        $colMap = [];

        foreach ($rows as $i => $row) {
            $found = false;
            foreach ($row as $idx => $cell) {
                $val = strtoupper(trim((string)$cell));
                if (in_array($val, ['TANGGAL', 'DATE'])) { $colMap['tanggal'] = $idx; $found = true; }
                if (in_array($val, ['OPERATOR_HD', 'OPERATOR', 'NRP'])) { $colMap['nrp'] = $idx; $found = true; }
                if (in_array($val, ['OPERATOR_EX', 'NAMA'])) { $colMap['nama'] = $idx; }
                if (in_array($val, ['LOADER', 'EXCAVATOR', 'ALAT_MUAT'])) { $colMap['loader'] = $idx; }
                if (in_array($val, ['JARAK', 'DISTANCE'])) { $colMap['jarak'] = $idx; }
                if (in_array($val, ['KODE_UNIT', 'UNIT', 'HAULER'])) { $colMap['kode_unit'] = $idx; }
                if (in_array($val, ['RIT', 'RITASE'])) { $colMap['rit'] = $idx; }
                if (in_array($val, ['TOTAL_BCM', 'BCM'])) { $colMap['total_bcm'] = $idx; }
                if (in_array($val, ['TOTAL_TON', 'TON'])) { $colMap['total_ton'] = $idx; }
            }
            if ($found && isset($colMap['tanggal'])) {
                $headerIdx = $i;
                break;
            }
        }

        if ($headerIdx === -1) {
            return ['imported' => 0, 'skipped' => count($rows), 'error' => 'Header kolom tidak dikenali'];
        }

        $imported = 0;
        $skipped = 0;
        $empCache = Employee::pluck('id', 'nrp')->toArray();

        DB::beginTransaction();
        try {
            for ($i = $headerIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nrpRaw = trim((string)($row[$colMap['nrp'] ?? -1] ?? ''));
                if ($nrpRaw === '') {
                    $skipped++;
                    continue;
                }

                $nrp = preg_replace('/[^A-Za-z0-9]/', '', $nrpRaw);
                $tanggal = $this->parseDate($row[$colMap['tanggal'] ?? -1] ?? '');
                if (!$tanggal) {
                    $skipped++;
                    continue;
                }

                $loader = trim((string)($row[$colMap['loader'] ?? -1] ?? ''));
                $kodeUnit = trim((string)($row[$colMap['kode_unit'] ?? -1] ?? ''));
                $jarak = $this->parseFloat($row[$colMap['jarak'] ?? -1] ?? 0);
                $rit = $this->parseFloat($row[$colMap['rit'] ?? -1] ?? 0);
                $bcm = $this->parseFloat($row[$colMap['total_bcm'] ?? -1] ?? 0);
                $ton = $this->parseFloat($row[$colMap['total_ton'] ?? -1] ?? 0);
                $nama = trim((string)($row[$colMap['nama'] ?? -1] ?? ''));

                $roleOp = (stripos($loader, 'EXCA') !== false || stripos($kodeUnit, 'EX') === 0 || stripos($kodeUnit, 'E') === 0) ? 'EXCA' : 'HAULER';

                QorDpr::create([
                    'employee_id' => $empCache[$nrp] ?? null,
                    'nrp' => $nrp,
                    'nama' => $nama ?: null,
                    'role_op' => $roleOp,
                    'tanggal' => $tanggal,
                    'loader' => $loader ?: null,
                    'jarak' => $jarak,
                    'distance' => $jarak,
                    'kode_unit' => $kodeUnit ?: null,
                    'rit' => $rit,
                    'total_bcm' => $bcm,
                    'total_ton' => $ton,
                    'source' => strtoupper($source),
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public function importSapHm(UploadedFile $file): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];
        if (empty($rows)) return ['imported' => 0, 'skipped' => 0];

        $headerIdx = -1;
        $colMap = [];

        foreach ($rows as $i => $row) {
            $found = false;
            foreach ($row as $idx => $cell) {
                $val = strtoupper(trim((string)$cell));
                if (str_contains($val, 'OPERATOR') || str_contains($val, 'NRP')) { $colMap['nrp'] = $idx; $found = true; }
                if (str_contains($val, 'MEASUREMENT') || str_contains($val, 'DOC')) { $colMap['measurement_doc'] = $idx; }
                if (str_contains($val, 'NAMA') || str_contains($val, 'NAME')) { $colMap['nama'] = $idx; }
                if (str_contains($val, 'UNIT') && !str_contains($val, '2')) { $colMap['kode_unit'] = $idx; }
                if (str_contains($val, 'ALAT') || str_contains($val, 'MUAT')) { $colMap['alat_muat'] = $idx; }
                if (str_contains($val, 'TANGGAL') || str_contains($val, 'DATE')) { $colMap['tanggal'] = $idx; }
                if (str_contains($val, 'HOUR') || str_contains($val, 'TIME')) { $colMap['hour_time'] = $idx; }
                if (str_contains($val, 'AKTIVITAS') || str_contains($val, 'ACTIVITY')) { $colMap['aktivitas'] = $idx; }
            }
            if ($found && isset($colMap['nrp'])) {
                $headerIdx = $i;
                break;
            }
        }

        if ($headerIdx === -1) return ['imported' => 0, 'skipped' => count($rows), 'error' => 'Header SAP tidak ditemukan'];

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nrpRaw = trim((string)($row[$colMap['nrp'] ?? -1] ?? ''));
                if ($nrpRaw === '') { $skipped++; continue; }

                $nrp = preg_replace('/[^A-Za-z0-9]/', '', $nrpRaw);
                $tanggal = $this->parseDate($row[$colMap['tanggal'] ?? -1] ?? '');
                if (!$tanggal) { $skipped++; continue; }

                $hour = $this->parseFloat($row[$colMap['hour_time'] ?? -1] ?? 0);

                QorSapHm::create([
                    'measurement_doc' => trim((string)($row[$colMap['measurement_doc'] ?? -1] ?? '')) ?: null,
                    'nrp' => $nrp,
                    'nama' => trim((string)($row[$colMap['nama'] ?? -1] ?? '')) ?: null,
                    'kode_unit' => trim((string)($row[$colMap['kode_unit'] ?? -1] ?? '')) ?: null,
                    'alat_muat' => trim((string)($row[$colMap['alat_muat'] ?? -1] ?? '')) ?: null,
                    'tanggal' => $tanggal,
                    'hour_time' => $hour,
                    'aktivitas' => trim((string)($row[$colMap['aktivitas'] ?? -1] ?? '')) ?: null,
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public function importAtr(UploadedFile $file): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];
        if (empty($rows)) return ['imported' => 0, 'skipped' => 0];

        $headerIdx = -1;
        $colMap = [];

        foreach ($rows as $i => $row) {
            $found = false;
            foreach ($row as $idx => $cell) {
                $val = strtoupper(trim((string)$cell));
                if ($val === 'NRP') { $colMap['nrp'] = $idx; $found = true; }
                if ($val === 'NAME' || $val === 'NAMA') { $colMap['nama'] = $idx; }
                if ($val === 'POSITION' || $val === 'POSISI' || $val === 'JABATAN') { $colMap['posisi'] = $idx; }
                if ($val === 'DATE' || $val === 'TANGGAL') { $colMap['tanggal'] = $idx; }
                if ($val === 'CODE' || $val === 'STATUS') { $colMap['code'] = $idx; }
                if ($val === 'DEPT') { $colMap['dept'] = $idx; }
                if ($val === 'COMPANY') { $colMap['company'] = $idx; }
            }
            if ($found && isset($colMap['nrp']) && isset($colMap['tanggal'])) {
                $headerIdx = $i;
                break;
            }
        }

        if ($headerIdx === -1) return ['imported' => 0, 'skipped' => count($rows), 'error' => 'Header ATR tidak ditemukan'];

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($i = $headerIdx + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nrpRaw = trim((string)($row[$colMap['nrp'] ?? -1] ?? ''));
                if ($nrpRaw === '') { $skipped++; continue; }

                $nrp = preg_replace('/[^A-Za-z0-9]/', '', $nrpRaw);
                $tanggal = $this->parseDate($row[$colMap['tanggal'] ?? -1] ?? '');
                if (!$tanggal) { $skipped++; continue; }

                QorAtrAttendance::create([
                    'nrp' => $nrp,
                    'nama' => trim((string)($row[$colMap['nama'] ?? -1] ?? '')) ?: null,
                    'posisi' => trim((string)($row[$colMap['posisi'] ?? -1] ?? '')) ?: null,
                    'tanggal' => $tanggal,
                    'code' => trim((string)($row[$colMap['code'] ?? -1] ?? '')) ?: null,
                    'dept' => trim((string)($row[$colMap['dept'] ?? -1] ?? '')) ?: null,
                    'company' => trim((string)($row[$colMap['company'] ?? -1] ?? '')) ?: null,
                ]);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function parseDate(mixed $val): ?string
    {
        if (empty($val)) return null;
        if (is_numeric($val)) {
            // Excel serial date number
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
        }
        $ts = strtotime((string)$val);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function parseFloat(mixed $val): float
    {
        if (empty($val)) return 0.0;
        $clean = str_replace([' ', ','], ['', '.'], (string)$val);
        return is_numeric($clean) ? (float)$clean : 0.0;
    }
}
