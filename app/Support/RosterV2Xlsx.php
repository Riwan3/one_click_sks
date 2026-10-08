<?php

namespace App\Support;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use RuntimeException;
use Throwable;
use ZipArchive;

// Baca/tulis Excel untuk import Roster V2 (port V1: pembaca memakai PhpSpreadsheet milik project, bukan SimpleXLSX).
// File dicek dulu (ukuran, struktur zip, total ukuran terdekompresi, nama entri) untuk menolak file berbahaya / zip-bomb.
// Tulis: template header & tabel export dirakit dari XML statis via ZipArchive.

class RosterV2Xlsx
{
    const MAX_FILE_BYTES   = 2097152;   // 2 MB
    const MAX_UNZIPPED     = 31457280;  // 30 MB total terdekompresi
    const MAX_ZIP_ENTRIES  = 200;
    const MAX_DATA_ROWS    = 5000;

    /** Header template, urutan kolom A..G. */
    const HEADERS = ['NRP', 'Nama', 'Jabatan', 'Tanggal', 'Shift', 'Unit', 'Section'];

    /**
     * Baca file upload (path sementara) menjadi baris terstruktur.
     * @return array ['rows' => [ ['row'=>int,'nrp'=>..,'nama'=>..,'jabatan'=>..,'tanggal'=>..,'shift'=>..,'unit'=>..,'section'=>..], ... ],
     *                'ignored_headers' => [..]]
     * @throws InvalidArgumentException pesan aman untuk user.
     */
    public static function readImportFile($path, $originalName) {
        if (strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION)) !== 'xlsx') {
            throw new InvalidArgumentException('File harus berformat .xlsx.');
        }
        $size = @filesize($path);
        if (!$size) throw new InvalidArgumentException('File kosong.');
        if ($size > self::MAX_FILE_BYTES) {
            throw new InvalidArgumentException('Ukuran file maksimal 2 MB.');
        }
        $fh = @fopen($path, 'rb');
        $magic = $fh ? fread($fh, 4) : '';
        if ($fh) fclose($fh);
        if ($magic !== "PK\x03\x04") {
            throw new InvalidArgumentException('File bukan Excel .xlsx yang valid.');
        }
        self::assertSafeZip($path);

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadEmptyCells(false);
            $sheet = $reader->load($path)->getSheet(0);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('File Excel tidak dapat dibaca. Pastikan formatnya .xlsx dan tidak rusak.');
        }

        // (nomor baris Excel asli, kolom) -> nilai; preview harus menunjuk baris Excel yang benar.
        $grid = [];
        foreach ($sheet->getRowIterator() as $rowNode) {
            $excelRow = $rowNode->getRowIndex();
            $cells = $rowNode->getCellIterator();
            $cells->setIterateOnlyExistingCells(true);
            foreach ($cells as $cell) {
                $val = trim((string)$cell->getCalculatedValue());
                if ($val === '') continue;
                if (is_numeric($val) && Date::isDateTime($cell)) {
                    $val = Date::excelToDateTimeObject((float)$val)->format('Y-m-d');
                }
                $grid[$excelRow][Coordinate::columnIndexFromString($cell->getColumn()) - 1] = $val;
            }
            if (count($grid) > self::MAX_DATA_ROWS + 50) {
                throw new InvalidArgumentException('File terlalu besar. Maksimal ' . self::MAX_DATA_ROWS . ' baris data.');
            }
        }
        if (!$grid) throw new InvalidArgumentException('File tidak berisi data.');

        ksort($grid);
        $headerRowNo = array_key_first($grid);
        $map = [];      // kolom -> nama field
        $ignored = [];
        $alias = ['nrp' => 'nrp', 'nama' => 'nama', 'jabatan' => 'jabatan', 'tanggal' => 'tanggal',
                  'shift' => 'shift', 'unit' => 'unit', 'section' => 'section'];
        foreach ($grid[$headerRowNo] as $col => $name) {
            $key = strtolower(trim($name));
            if (isset($alias[$key]) && !in_array($alias[$key], $map, true)) {
                $map[$col] = $alias[$key];
            } else {
                $ignored[] = $name;
            }
        }
        $missing = array_diff(['nrp', 'tanggal', 'shift'], $map);
        if ($missing) {
            throw new InvalidArgumentException(
                'Header tidak sesuai template. Kolom wajib tidak ditemukan: ' . strtoupper(implode(', ', $missing))
                . '. Gunakan header: ' . implode(' | ', self::HEADERS) . '.'
            );
        }

        $rows = [];
        foreach ($grid as $excelRow => $cells) {
            if ($excelRow === $headerRowNo) continue;
            $r = ['row' => $excelRow, 'nrp' => '', 'nama' => '', 'jabatan' => '', 'tanggal' => '', 'shift' => '', 'unit' => '', 'section' => ''];
            foreach ($cells as $col => $val) {
                if (isset($map[$col])) $r[$map[$col]] = $val;
            }
            $rows[] = $r;
        }
        if (!$rows) throw new InvalidArgumentException('File tidak berisi baris data (hanya header).');
        if (count($rows) > self::MAX_DATA_ROWS) {
            throw new InvalidArgumentException('Maksimal ' . self::MAX_DATA_ROWS . ' baris data per import.');
        }
        return ['rows' => $rows, 'ignored_headers' => $ignored];
    }

    private static function assertSafeZip($path) {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new InvalidArgumentException('File Excel rusak atau bukan .xlsx.');
        }
        try {
            if ($zip->numFiles > self::MAX_ZIP_ENTRIES) {
                throw new InvalidArgumentException('Struktur file Excel tidak wajar.');
            }
            $total = 0;
            $hasContentTypes = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st = $zip->statIndex($i);
                $name = (string)($st['name'] ?? '');
                if ($name === '[Content_Types].xml') $hasContentTypes = true;
                if (strpos($name, '..') !== false || $name[0] === '/' || strpos($name, '\\') !== false) {
                    throw new InvalidArgumentException('File Excel berisi entri yang tidak diizinkan.');
                }
                $total += (int)($st['size'] ?? 0);
                if ($total > self::MAX_UNZIPPED) {
                    throw new InvalidArgumentException('File Excel terlalu besar setelah diekstrak.');
                }
            }
            if (!$hasContentTypes) {
                throw new InvalidArgumentException('File bukan Excel .xlsx yang valid.');
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Bangun .xlsx satu sheet ke path sementara; return path.
     * $headers: judul kolom; $rows: array baris (nilai skalar); $dateCols: indeks kolom (0-based) berisi 'YYYY-MM-DD' -> sel tanggal Excel;
     * kolom yang seluruhnya angka bulat tanpa nol di depan ditulis sebagai angka, sisanya teks.
     */
    public static function buildTable($sheetName, array $headers, array $rows, array $dateCols = []) {
        $tmp = tempnam(sys_get_temp_dir(), 'rv2tbl');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Gagal membuat file Excel.');
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $zip->addFromString('[Content_Types].xml', $xml
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', $xml
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $xml
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . htmlspecialchars(substr($sheetName, 0, 31), ENT_XML1) . '" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $xml
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        // xf 0 = biasa, 1 = judul tebal, 2 = tanggal yyyy-mm-dd
        $zip->addFromString('xl/styles.xml', $xml
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="yyyy\-mm\-dd"/></numFmts>'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs></styleSheet>');
        $colName = function ($i) { $s = ''; for ($i++; $i > 0; $i = intdiv($i - 1, 26)) $s = chr(65 + ($i - 1) % 26) . $s; return $s; };
        $numeric = [];   // kolom yang aman ditulis sebagai angka
        foreach ($headers as $ci => $_) {
            $ok = $rows !== [];
            foreach ($rows as $r) { $v = (string)($r[$ci] ?? ''); if (!preg_match('/^(0|[1-9]\d{0,14})$/', $v)) { $ok = false; break; } }
            $numeric[$ci] = $ok;
        }
        $widths = [];
        $sheet = '<row r="1">';
        foreach ($headers as $ci => $h) {
            $sheet .= '<c r="' . $colName($ci) . '1" s="1" t="inlineStr"><is><t>' . htmlspecialchars((string)$h, ENT_XML1) . '</t></is></c>';
            $widths[$ci] = max(10, strlen((string)$h) + 2);
        }
        $sheet .= '</row>';
        $base = new DateTimeImmutable('1899-12-30', new DateTimeZone('UTC'));
        foreach ($rows as $ri => $r) {
            $sheet .= '<row r="' . ($ri + 2) . '">';
            foreach ($headers as $ci => $_) {
                $v = (string)($r[$ci] ?? ''); $ref = $colName($ci) . ($ri + 2);
                $widths[$ci] = max($widths[$ci], min(50, strlen($v) + 2));
                if ($v === '') continue;
                $d = in_array($ci, $dateCols, true) ? DateTimeImmutable::createFromFormat('!Y-m-d', $v, new DateTimeZone('UTC')) : false;
                if ($d) $sheet .= '<c r="' . $ref . '" s="2"><v>' . (int)$base->diff($d)->format('%a') . '</v></c>';
                elseif ($numeric[$ci]) $sheet .= '<c r="' . $ref . '"><v>' . $v . '</v></c>';
                else $sheet .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . htmlspecialchars($v, ENT_XML1) . '</t></is></c>';
            }
            $sheet .= '</row>';
        }
        $cols = '';
        foreach ($widths as $ci => $w) $cols .= '<col min="' . ($ci + 1) . '" max="' . ($ci + 1) . '" width="' . (in_array($ci, $dateCols, true) ? 12 : $w) . '" customWidth="1"/>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $xml
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . $sheet . '</sheetData></worksheet>');
        $zip->close();
        return $tmp;
    }

    /** Bangun file template .xlsx (header saja) ke path sementara; return path. */
    public static function buildTemplate() {
        $tmp = tempnam(sys_get_temp_dir(), 'rv2tpl');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat template.');
        }
        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Roster" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>');
        $cells = '';
        foreach (self::HEADERS as $i => $h) {
            $cells .= '<c r="' . chr(65 + $i) . '1" t="inlineStr"><is><t>' . htmlspecialchars($h, ENT_XML1) . '</t></is></c>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols><col min="1" max="7" width="18" customWidth="1"/></cols>'
            . '<sheetData><row r="1">' . $cells . '</row></sheetData></worksheet>');
        $zip->close();
        return $tmp;
    }
}
