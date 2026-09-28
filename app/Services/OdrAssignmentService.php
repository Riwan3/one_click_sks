<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OdrAssignment;
use App\Models\OdrMasterAktivitas;
use App\Models\OdrMasterArea;
use App\Models\OdrMasterUnit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Assignment harian & checklist Daily Compliance One-Reporting
 * (padanan database/odr/index.php + daily_compliance.php v1).
 */
class OdrAssignmentService
{
    /** Area default v1 bila master area masih kosong. */
    private const FALLBACK_AREAS = ['MFOB', 'MFCO', 'Disposal', 'HRM', 'Dewatering', 'Land Clearing', 'Manpower & Office'];

    public const TEMPLATE_HEADERS = ['Tanggal (YYYY-MM-DD)', 'Area', 'Unit', 'Nama Pengawas', 'Keterangan', 'Shift (Shift 1/Shift 2)'];

    public function __construct(private OdrMasterService $masterService) {}

    public function normalizeFilters(array $input): array
    {
        $text = fn ($key) => trim((string) ($input[$key] ?? ''));
        $tanggal = $text('tanggal');

        return [
            'tanggal' => strtotime($tanggal) ? Carbon::parse($tanggal)->toDateString() : now()->toDateString(),
            'shift' => in_array($text('shift'), OdrAssignment::SHIFTS, true) ? $text('shift') : '',
            'area' => $text('area'),
            'status' => in_array($text('status'), ['belum', 'progress', 'selesai'], true) ? $text('status') : '',
            'pengawas' => $text('pengawas'),
        ];
    }

    /** Sinkronkan kolom status dari checklist task untuk 1 tanggal (sama seperti v1 sebelum render). */
    public function syncStatuses(string $tanggal): void
    {
        OdrAssignment::whereDate('tanggal', $tanggal)->get()->each(function (OdrAssignment $row) {
            $status = $row->compliance()['status'];
            if ($row->status !== $status) $row->update(['status' => $status]);
        });
    }

    public function list(array $filters): Collection
    {
        return OdrAssignment::query()->filter($filters)->get();
    }

    /** KPI Daily Compliance: hanya tanggal + shift (filter area/status tidak memengaruhi). */
    public function kpi(string $tanggal, string $shift): array
    {
        $counts = OdrAssignment::query()->filter(['tanggal' => $tanggal, 'shift' => $shift])->reorder()
            ->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'selesai' => (int) ($counts['selesai'] ?? 0),
            'progress' => (int) ($counts['progress'] ?? 0),
            'belum' => (int) ($counts['belum'] ?? 0),
        ];
    }

    public function toggleTask(OdrAssignment $assignment, int $taskIndex, bool $isDone): array
    {
        $flags = $assignment->doneFlags();
        if (array_key_exists($taskIndex, $flags)) $flags[$taskIndex] = $isDone;

        $assignment->task_done = json_encode($flags);
        $assignment->status = $assignment->compliance()['status'];
        $assignment->save();

        return $assignment->compliance();
    }

    public function create(array $data): OdrAssignment
    {
        return OdrAssignment::create([...$this->attributes($data), 'status' => 'belum']);
    }

    /** Edit tidak mengubah checklist; status mengikuti pilihan form (lalu disinkronkan di Daily Compliance). */
    public function update(OdrAssignment $assignment, array $data): void
    {
        $assignment->update([...$this->attributes($data), 'status' => $data['status'] ?? $assignment->status]);
    }

    /**
     * Import xlsx/csv dengan kolom template v1 (A Tanggal, B Area, C Unit, D Pengawas, E Keterangan, F Shift).
     *
     * @return array{imported:int, skipped:int, errors:array<int,string>, last_date:?string}
     */
    public function import(UploadedFile $file, string $defaultShift): array
    {
        $rows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, false);
        array_shift($rows);

        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'last_date' => null];

        DB::transaction(function () use ($rows, $defaultShift, &$result) {
            foreach ($rows as $index => $row) {
                [$rawDate, $area, $unit, $pengawas, $keterangan, $shift] = array_map(fn ($v) => trim((string) $v), array_pad(array_slice($row, 0, 6), 6, ''));
                if ($rawDate === '' && $area === '' && $unit === '' && $pengawas === '') continue;

                $tanggal = $this->parseImportDate($rawDate);
                if (! $tanggal || $area === '' || $unit === '' || $pengawas === '') {
                    $result['skipped']++;
                    $result['errors'][] = 'Baris '.($index + 2).': data tidak lengkap atau tanggal tidak valid.';
                    continue;
                }

                OdrAssignment::create([
                    'tanggal' => $tanggal, 'area' => $area, 'unit' => $unit, 'pengawas' => $pengawas,
                    'keterangan' => $keterangan, 'shift' => $shift ?: ($defaultShift ?: 'Shift 1'), 'status' => 'belum',
                ]);
                $result['imported']++;
                $result['last_date'] = $tanggal;
            }
        });

        return $result;
    }

    public function areaOptions(): Collection
    {
        $areas = OdrMasterArea::where('aktif', true)->orderBy('nama')->pluck('nama');

        return $areas->isNotEmpty() ? $areas : collect(self::FALLBACK_AREAS);
    }

    /** Area yang pernah dipakai di assignment (untuk filter Daily Compliance). */
    public function usedAreas(): Collection
    {
        return OdrAssignment::whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area');
    }

    public function unitOptions(): Collection
    {
        return OdrMasterUnit::where('aktif', true)->orderBy('area')->orderBy('nama')->get()->map(fn ($u) => [
            'value' => $u->kode ? "{$u->kode} - {$u->nama}" : $u->nama,
            'label' => ($u->kode ? "{$u->kode} - " : '').$u->nama.($u->area ? " ({$u->area})" : ''),
            'area' => $u->area,
        ]);
    }

    public function aktivitasOptions(): Collection
    {
        return OdrMasterAktivitas::where('aktif', true)->orderBy('kategori')->orderBy('nama')->get()->map(fn ($a) => [
            'value' => $a->nama,
            'label' => ($a->kategori ? "[{$a->kategori}] " : '').$a->nama,
        ]);
    }

    /** Pengawas: karyawan aktif dengan jabatan Group Leader. */
    public function pengawasOptions(): Collection
    {
        return Employee::query()
            ->where(fn ($q) => $q->where('jabatan_ss6', 'like', '%GROUP LEADER%')->orWhere('jabatan_ss6', 'like', '%GL %')->orWhere('jabatan_ss6', 'GL'))
            ->where(fn ($q) => $q->whereNull('status_karyawan')->orWhere('status_karyawan', 'not like', '%INACTIVE%'))
            ->orderBy('name')
            ->pluck('name');
    }

    /** URL dokumen prosedur per nama aktivitas (dari Master Aktivitas). */
    public function aktivitasDocuments(): array
    {
        return OdrMasterAktivitas::whereNotNull('keterangan')->where('keterangan', '!=', '')->get(['nama', 'keterangan'])
            ->mapWithKeys(fn ($a) => [$a->nama => $this->masterService->documentUrls($this->masterService->documentPaths($a->keterangan))])
            ->filter()
            ->all();
    }

    private function attributes(array $data): array
    {
        $tasks = array_values(array_filter(array_map('trim', $data['aktivitas'] ?? [])));

        return [
            'tanggal' => $data['tanggal'],
            'shift' => $data['shift'] ?: 'Shift 1',
            'area' => trim($data['area']),
            'unit' => trim($data['unit']),
            'pengawas' => trim($data['pengawas']),
            'aktivitas' => $tasks ? json_encode($tasks, JSON_UNESCAPED_UNICODE) : null,
            'keterangan' => trim((string) ($data['keterangan'] ?? '')),
        ];
    }

    private function parseImportDate(string $raw): ?string
    {
        if ($raw === '') return null;
        if (is_numeric($raw)) {
            $unix = ((float) $raw - 25569) * 86400;

            return $unix > 0 ? gmdate('Y-m-d', (int) $unix) : null;
        }

        return strtotime($raw) ? Carbon::parse($raw)->toDateString() : null;
    }
}
