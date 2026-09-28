<?php

namespace App\Services;

use App\Models\OdrAssignment;
use App\Models\OdrGlSetting;
use App\Support\OdrGlAllocationParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Setting GL One-Reporting: mapping PIC Front/Disposal per tanggal+shift & sinkron ke Daily Assignment. */
class OdrGlSettingService
{
    /** Checklist default assignment hasil sinkron, sama seperti v1. */
    private const DEFAULT_TASKS = ['GMP', 'Inspeksi Area'];

    private const FRONT_AREA = 'Front Loading';

    private const DISPOSAL_AREA = 'Disposal';

    public function __construct(private OdrGlAllocationParser $parser) {}

    public function rows(string $tanggal, string $shift): Collection
    {
        return OdrGlSetting::whereDate('tanggal', $tanggal)->where('shift', $shift)->orderBy('area')->orderBy('unit')->get();
    }

    /** Tanggal + shift yang pernah disimpan (dropdown "Riwayat Tersimpan"). */
    public function history(): Collection
    {
        return OdrGlSetting::query()->select('tanggal', 'shift')->distinct()->orderByDesc('tanggal')->orderBy('shift')->get()
            ->map(fn ($r) => ['tanggal' => substr((string) $r->tanggal, 0, 10), 'shift' => $r->shift]);
    }

    /**
     * Parse kedua box, ganti mapping tanggal+shift, lalu sinkron ulang assignment hasil GL.
     *
     * @return array{rows:int, front:int, disposal:int, assignments:int}|null null bila tidak ada baris yang terbaca
     */
    public function parseAndSave(string $tanggal, string $shift, string $frontText, string $disposalText): ?array
    {
        $mapping = $this->merge($this->parser->front($frontText), $this->parser->disposal($disposalText));
        if ($mapping === []) return null;

        [$frontAssign, $disposalAssign] = $this->assignmentTargets($mapping);

        DB::transaction(function () use ($tanggal, $shift, $mapping, $frontAssign, $disposalAssign) {
            OdrGlSetting::whereDate('tanggal', $tanggal)->where('shift', $shift)->delete();
            foreach ($mapping as $row) {
                OdrGlSetting::create([...$row, 'tanggal' => $tanggal, 'shift' => $shift]);
            }

            $this->deleteSyncedAssignments($tanggal, $shift);
            foreach ($frontAssign as $unit => $pic) $this->createAssignment($tanggal, $shift, self::FRONT_AREA, $unit, $pic);
            foreach ($disposalAssign as $areaOrUnit => $pic) $this->createAssignment($tanggal, $shift, self::DISPOSAL_AREA, $areaOrUnit, $pic);
        });

        return [
            'rows' => count($mapping),
            'front' => count(array_filter($mapping, fn ($r) => $r['front_pic'] !== '')),
            'disposal' => count(array_filter($mapping, fn ($r) => $r['disposal_pic'] !== '')),
            'assignments' => count($frontAssign) + count($disposalAssign),
        ];
    }

    public function deleteBatch(string $tanggal, string $shift): void
    {
        DB::transaction(function () use ($tanggal, $shift) {
            OdrGlSetting::whereDate('tanggal', $tanggal)->where('shift', $shift)->delete();
            $this->deleteSyncedAssignments($tanggal, $shift);
        });
    }

    /** Gabungkan hasil parse per kunci area+unit menjadi baris Front PIC / Disposal PIC. */
    private function merge(array $frontRows, array $disposalRows): array
    {
        $mapping = [];
        foreach (['front_pic' => $frontRows, 'disposal_pic' => $disposalRows] as $column => $rows) {
            foreach ($rows as $row) {
                $key = $row['area'].'||'.$row['unit'];
                $mapping[$key] ??= ['area' => $row['area'], 'unit' => $row['unit'], 'front_pic' => '', 'disposal_pic' => ''];
                $mapping[$key][$column] = $row['pic'];
            }
        }

        return array_values($mapping);
    }

    /** Front: 1 assignment per unit; Disposal: 1 per area (atau unit bila area kosong). */
    private function assignmentTargets(array $mapping): array
    {
        $front = [];
        $disposal = [];
        foreach ($mapping as $row) {
            if ($row['front_pic'] !== '' && $row['unit'] !== '') $front[$row['unit']] = $row['front_pic'];

            $disposalKey = $row['area'] !== '' ? $row['area'] : $row['unit'];
            if ($row['disposal_pic'] !== '' && $disposalKey !== '') $disposal[$disposalKey] = $row['disposal_pic'];
        }

        return [$front, $disposal];
    }

    private function deleteSyncedAssignments(string $tanggal, string $shift): void
    {
        OdrAssignment::whereDate('tanggal', $tanggal)->where('shift', $shift)->where('gl_synced', true)->delete();
    }

    private function createAssignment(string $tanggal, string $shift, string $area, string $unit, string $pic): void
    {
        OdrAssignment::create([
            'tanggal' => $tanggal,
            'shift' => $shift,
            'area' => $area,
            'unit' => $unit,
            'pengawas' => $pic,
            'status' => 'belum',
            'gl_synced' => true,
            'aktivitas' => json_encode(self::DEFAULT_TASKS, JSON_UNESCAPED_UNICODE),
            'task_done' => json_encode(array_fill(0, count(self::DEFAULT_TASKS), false)),
        ]);
    }
}
