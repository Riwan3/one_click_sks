<?php

namespace App\Services;

use App\Models\OpdMinimalGrade;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MinimalGradeService
{
    private const HEADER_SCAN_LIMIT = 40;

    private const MAX_IMPORT_ROWS = 5000;

    /** Nama header ternormalisasi => kolom tabel. */
    private const HEADERS = [
        'UNIT' => 'unit',
        'GRADEMINMAX' => 'grade_min_max',
        'GRADEMIN' => 'grade_min',
        'CLUSTERUNIT' => 'cluster_unit',
    ];

    public function __construct(private MonitoringGradeSyncService $sheets) {}

    public function list(?string $keyword): Collection
    {
        return OpdMinimalGrade::search($keyword)->orderBy('unit')->orderBy('id')->get();
    }

    public function save(array $data, string $user, ?OpdMinimalGrade $row = null): OpdMinimalGrade
    {
        $values = $this->normalize($data);

        return $row
            ? tap($row)->update($values)
            : OpdMinimalGrade::create([...$values, 'created_by' => $user]);
    }

    /**
     * Impor dari file Excel/CSV atau link Google Sheet.
     *
     * @return array{inserted: int, updated: int}
     */
    public function import(?UploadedFile $file, ?string $sheetUrl, bool $replaceAll, string $user): array
    {
        $rows = $this->extractRows($this->readSource($file, $sheetUrl));

        return DB::transaction(function () use ($rows, $replaceAll, $user) {
            if ($replaceAll) {
                OpdMinimalGrade::query()->delete();
            }

            $counts = ['inserted' => 0, 'updated' => 0];
            foreach ($rows as $row) {
                $existing = OpdMinimalGrade::whereRaw('UPPER(TRIM(unit)) = ?', [mb_strtoupper($row['unit'])])
                    ->whereRaw("UPPER(TRIM(COALESCE(cluster_unit, ''))) = ?", [mb_strtoupper((string) $row['cluster_unit'])])
                    ->first();

                $existing
                    ? $existing->update(['grade_min_max' => $row['grade_min_max'], 'grade_min' => $row['grade_min']])
                    : OpdMinimalGrade::create([...$row, 'created_by' => $user]);
                $counts[$existing ? 'updated' : 'inserted']++;
            }

            return $counts;
        });
    }

    /** @return array<int, array<int, mixed>> */
    private function readSource(?UploadedFile $file, ?string $sheetUrl): array
    {
        if ($sheetUrl) {
            [$rows, $error] = $this->sheets->fetchCsvRows($sheetUrl);
            if ($error) {
                throw new \InvalidArgumentException($error);
            }

            return $rows;
        }

        if (! $file) {
            throw new \InvalidArgumentException('Isi link Google Sheet atau pilih file Excel/CSV.');
        }

        return Excel::toArray(new \stdClass, $file)[0] ?? [];
    }

    /** Cari baris header lalu petakan baris data di bawahnya. Bila ada dua kolom UNIT, yang pertama dipakai. */
    private function extractRows(array $sheet): array
    {
        $sheet = array_values($sheet);
        [$headerIndex, $columns] = $this->findHeader($sheet);
        if ($headerIndex === null) {
            throw new \InvalidArgumentException('Header tidak ditemukan. Baris judul harus berisi kolom: UNIT, GRADE MIN-MAX, GRADE MIN, CLUSTER UNIT.');
        }

        $rows = [];
        foreach (array_slice($sheet, $headerIndex + 1, self::MAX_IMPORT_ROWS) as $line) {
            $values = array_map(fn ($column) => trim((string) ($line[$column] ?? '')), $columns);
            if (($values['unit'] ?? '') === '') {
                continue;
            }
            $rows[] = $this->normalize($values);
        }

        if ($rows === []) {
            throw new \InvalidArgumentException('Tidak ada baris data (kolom UNIT kosong semua).');
        }

        return $rows;
    }

    /** @return array{0: ?int, 1: array<string, int>} */
    private function findHeader(array $sheet): array
    {
        foreach (array_slice($sheet, 0, self::HEADER_SCAN_LIMIT) as $index => $line) {
            $columns = [];
            foreach ((array) $line as $position => $cell) {
                $field = self::HEADERS[preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $cell)))] ?? null;
                if ($field && ! isset($columns[$field])) {
                    $columns[$field] = $position;
                }
            }
            if (isset($columns['unit']) && count($columns) >= 2) {
                return [$index, $columns];
            }
        }

        return [null, []];
    }

    private function normalize(array $data): array
    {
        $blankToNull = fn (?string $value, int $max) => ($value = trim((string) $value)) === '' ? null : mb_substr($value, 0, $max);

        return [
            'unit' => mb_substr(trim((string) $data['unit']), 0, 150),
            'grade_min_max' => $blankToNull($data['grade_min_max'] ?? null, 100),
            'grade_min' => $blankToNull($data['grade_min'] ?? null, 50),
            'cluster_unit' => $blankToNull($data['cluster_unit'] ?? null, 150),
        ];
    }
}
