<?php

namespace App\Http\Controllers;

use App\Exceptions\RosterV2NotFound;
use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\ChangeRosterStatusRequest;
use App\Http\Requests\CommitRosterImportRequest;
use App\Http\Requests\CommitRosterSheetRequest;
use App\Http\Requests\DuplicateRosterRequest;
use App\Http\Requests\FillRosterCellsRequest;
use App\Http\Requests\ManageRosterRequest;
use App\Http\Requests\MoveRosterMemberRequest;
use App\Http\Requests\PreviewRosterImportRequest;
use App\Http\Requests\PreviewRosterSheetRequest;
use App\Http\Requests\SaveRosterAllocationRequest;
use App\Http\Requests\SaveRosterDetailRequest;
use App\Http\Requests\SaveRosterMemberRotationRequest;
use App\Http\Requests\SaveRosterMemberUnitRequest;
use App\Http\Requests\SaveRosterRequest;
use App\Http\Requests\SaveRosterSectionTargetRequest;
use App\Http\Requests\SaveRosterVisibilityRequest;
use App\Http\Requests\SearchRosterEmployeeRequest;
use App\Http\Requests\SetRosterCellRequest;
use App\Http\Requests\ShowRosterRequest;
use App\Http\Requests\StoreRosterMemberRequest;
use App\Http\Requests\StoreRosterSectionRequest;
use App\Http\Requests\SyncRosterMoncuRequest;
use App\Http\Resources\RosterChangelogResource;
use App\Http\Resources\RosterDetailResource;
use App\Http\Resources\RosterMatrixResource;
use App\Http\Resources\RosterResource;
use App\Services\MoncuService;
use App\Services\RosterV2Service;
use App\Services\RosterV2SheetImportService;
use App\Support\MenuAccess;
use App\Support\RosterV2Xlsx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/** One-Roster > Roster (daftar, matriks, upload, riwayat, import, semua aksi tulis). Controller hanya orkestrasi ke RosterV2Service. */
class OneRosterController extends Controller
{
    use HandlesMoncuErrors;

    private const XLSX_MIME = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function __construct(private RosterV2Service $service) {}

    // ------------------------------------------------------------- Halaman

    public function index(ShowRosterRequest $request): Response
    {
        $filters = [
            'date_from' => $request->text('date_from'),
            'date_to' => $request->text('date_to'),
            'roster_group' => $request->text('roster_group'),
            'status' => $request->text('status'),
        ];
        $rosters = $this->service->listRosters($filters);
        $isArchived = fn (array $r) => $r['status'] === 'archived';

        return Inertia::render('OneRoster/Roster/Index', [
            ...$this->layoutProps(),
            'summary' => $this->service->getSummary(),
            'groups' => $this->service->getGroups(),
            'filters' => $filters,
            'rosters' => RosterResource::collection(array_values(array_filter($rosters, fn ($r) => ! $isArchived($r))))->resolve(),
            'archivedRosters' => RosterResource::collection(array_values(array_filter($rosters, $isArchived)))->resolve(),
            'sheetRosters' => RosterResource::collection(
                array_values(array_filter($this->service->listRosters([]), fn ($r) => ! $isArchived($r)))
            )->resolve(),
            'canManage' => RosterV2Service::canManage(),
            'me' => $this->me(),
            'yearNow' => (int) date('Y'),
        ]);
    }

    public function show(ShowRosterRequest $request, int $id): Response|RedirectResponse
    {
        try {
            $head = $this->service->getRosterHeader($id);
        } catch (RosterV2NotFound $e) {
            return redirect('/one-roster/roster')->with('error', $e->getMessage());
        }

        $roster = $head['roster'];
        $tab = in_array($request->text('tab'), ['list', 'upload'], true) ? $request->text('tab') : 'matrix';
        $canEdit = RosterV2Service::canManage() && $roster['status'] !== 'archived';
        $sectionsReady = $this->service->sectionsReady();

        return Inertia::render('OneRoster/Roster/Show', [
            ...$this->layoutProps(),
            'roster' => (new RosterResource($roster))->resolve(),
            'totalDetail' => $head['total'],
            'tab' => $tab,
            'canManage' => RosterV2Service::canManage(),
            'canEdit' => $canEdit,
            'sectionsReady' => $sectionsReady,
            'me' => $this->me(),
            'shifts' => $canEdit ? $this->service->getShifts() : [],
            'months' => $canEdit ? $this->service->rosterMonths($roster) : [],
            'changelog' => fn () => $this->changelog($id),
            'list' => fn () => $tab === 'list' ? $this->listData($id, max(1, (int) $request->query('page', 1))) : null,
            'matrix' => fn () => $tab === 'matrix' && $sectionsReady ? $this->matrixData($request, $id) : null,
            'ctxQuery' => implode('', array_filter(['p', 'n'], fn ($flag) => str_contains($request->text('ctx'), $flag))),
            'upload' => fn () => $tab === 'upload' && $sectionsReady ? $this->uploadData($request, $id) : null,
            'step' => in_array($request->text('step'), ['1', '2', '3'], true) ? (int) $request->text('step') : 3,
        ]);
    }

    public function downloadUpload(ShowRosterRequest $request, int $id): BinaryFileResponse
    {
        $upload = $this->service->getUploadData($id, $this->uploadQuery($request));
        $name = 'upload_roster_'.preg_replace('/[^A-Za-z0-9_-]+/', '_', $upload['roster']['roster_group']).'_'.$upload['start'].'_'.$upload['end'].'.xlsx';
        $rows = array_map(fn ($r) => [$r['no'], $r['nrp'], $r['nama'], $r['idx'], $r['tanggal'], $r['status'], $r['unit'], $r['lookup']], $upload['rows']);
        $tmp = RosterV2Xlsx::buildTable('UPLOAD', ['No', 'NRP', 'Nama', 'Idx_tgl', 'Tanggal', 'Status', 'Unit', 'Lookup'], $rows, [4]);

        return response()->download($tmp, $name, ['Content-Type' => self::XLSX_MIME])->deleteFileAfterSend(true);
    }

    public function importTemplate(ManageRosterRequest $request): BinaryFileResponse
    {
        return response()->download(RosterV2Xlsx::buildTemplate(), 'template_import_roster_v2.xlsx', ['Content-Type' => self::XLSX_MIME])
            ->deleteFileAfterSend(true);
    }

    // --------------------------------------------------------- Aksi roster

    public function store(SaveRosterRequest $request): RedirectResponse
    {
        return $this->guardPage(function () use ($request) {
            $id = $this->service->createRoster($request->validated());

            return redirect("/one-roster/roster/{$id}")->with('success', 'Roster berhasil dibuat.');
        });
    }

    public function update(SaveRosterRequest $request, int $id): RedirectResponse
    {
        return $this->done(fn () => $this->service->updateRoster($id, $request->validated()), 'Roster berhasil diperbarui.');
    }

    public function status(ChangeRosterStatusRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        return $this->done(function () use ($data, $id) {
            if ($data['status'] === 'archived') {   // aksi tak bisa dibatalkan -> verifikasi ulang NRP + password akun yang login
                $this->service->verifyActorCredentials($data['auth_nrp'] ?? '', $data['auth_password'] ?? '');
            }
            $this->service->changeStatus($id, $data['status']);
        }, 'Status roster diperbarui.');
    }

    public function duplicate(DuplicateRosterRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        return $this->guardPage(function () use ($data, $request, $id) {
            $r = $this->service->duplicateRoster($id, $data['roster_group'] ?? '', $data['year'] ?? '', $request->boolean('create_moncu'));

            return redirect("/one-roster/roster/{$r['id']}")->with('success', $this->duplicateMessage($r));
        });
    }

    public function saveVisibility(SaveRosterVisibilityRequest $request, int $id): RedirectResponse
    {
        return $this->guardPage(function () use ($request, $id) {
            $list = $this->service->setVisibleMonths($id, $request->validated('months') ?? []);

            return back()->with('success', $list
                ? count($list).' bulan dibuka untuk operator/helper.'
                : 'Tidak ada bulan yang dibuka (roster kosong bagi operator/helper).');
        });
    }

    public function syncMoncu(SyncRosterMoncuRequest $request, MoncuService $moncu, int $id): RedirectResponse
    {
        $nrp = trim((string) $request->validated('nrp', '')) ?: null;

        return $this->guardPage(function () use ($moncu, $nrp, $id) {
            $r = $moncu->syncRosterV2LeaveStatus($nrp, $id);

            return back()->with('success', $this->syncMessage($r));
        });
    }

    // ------------------------------------------------------ Section & anggota

    public function storeSection(StoreRosterSectionRequest $request, int $id): RedirectResponse
    {
        return $this->done(fn () => $this->service->addSection($id, $request->validated('name', '')), 'Section ditambahkan.');
    }

    public function destroySection(ManageRosterRequest $request, int $id, int $section): RedirectResponse
    {
        return $this->done(fn () => $this->service->deleteSection($id, $section), 'Section dihapus.');
    }

    public function saveSectionTarget(SaveRosterSectionTargetRequest $request, int $id, int $section): RedirectResponse
    {
        return $this->done(
            fn () => $this->service->setSectionTargets($id, $section, $request->validated('target_siang', ''), $request->validated('target_malam', '')),
            'Target section disimpan.'
        );
    }

    public function storeMember(StoreRosterMemberRequest $request, MoncuService $moncu, int $id, int $section): RedirectResponse
    {
        $d = $request->validated();

        return $this->guardPage(function () use ($d, $moncu, $id, $section) {
            $nrp = trim((string) ($d['nrp'] ?? ''));
            $this->service->addMember($id, $section, $nrp, $d['unit'] ?? '', $d['kode_off'] ?? '', $d['work_pattern'] ?? 'ROTASI');

            return back()->with('success', $this->memberAddedMessage($moncu, $id, $nrp));
        });
    }

    public function moveMember(MoveRosterMemberRequest $request, int $id, int $member): RedirectResponse
    {
        $d = $request->validated();

        return $this->guardPage(function () use ($d, $id, $member) {
            $r = $this->service->moveMember($id, $member, (int) $d['section_id'], $d['position'] ?? null);

            return back()->with('success', 'Anggota dipindahkan ke nomor '.$r['position'].'.');
        });
    }

    public function saveMemberRotation(SaveRosterMemberRotationRequest $request, int $id, int $member): RedirectResponse
    {
        $d = $request->validated();
        $apply = $request->boolean('apply');

        return $this->guardPage(function () use ($d, $apply, $id, $member) {
            $r = $this->service->setMemberRotation($id, $member, $d['kode_off'] ?? '', $d['date_from'] ?? '', $d['date_to'] ?? '', $apply, $d['work_pattern'] ?? null);

            return back()->with('success', $this->rotationMessage($r, $apply));
        });
    }

    public function saveMemberUnit(SaveRosterMemberUnitRequest $request, int $id, int $member): JsonResponse
    {
        return $this->guardJson(fn () => ['unit' => $this->service->setMemberUnit($id, $member, $request->validated('unit', ''))]);
    }

    public function destroyMember(ManageRosterRequest $request, int $id, int $member): RedirectResponse
    {
        return $this->guardPage(function () use ($id, $member) {
            $n = $this->service->removeMember($id, $member);

            return back()->with('success', 'Anggota dikeluarkan dari roster ('.$n.' sel jadwal dihapus).');
        });
    }

    // ------------------------------------------------------------------ Sel

    public function setCell(SetRosterCellRequest $request, int $id): JsonResponse
    {
        $d = $request->validated();

        return $this->guardJson(fn () => $this->service->setCell($id, $d['nrp'] ?? '', $d['work_date'] ?? '', $d['shift_code'] ?? ''));
    }

    public function fillCells(FillRosterCellsRequest $request, int $id): RedirectResponse
    {
        $d = $request->validated();

        return $this->guardPage(function () use ($d, $id) {
            $n = $this->service->fillRange($id, $d['nrp'] ?? '', $d['date_from'] ?? '', $d['date_to'] ?? '', $d['shift_code'] ?? '');

            return back()->with('success', $n.' tanggal diproses.');
        });
    }

    // --------------------------------------------------- Daftar detail & alokasi

    public function storeDetail(SaveRosterDetailRequest $request, int $id): RedirectResponse
    {
        $d = $request->validated();

        return $this->done(
            fn () => $this->service->addDetail($id, $d['nrp'] ?? '', $d['work_date'] ?? '', $d['shift_code'] ?? '', $d['unit'] ?? '', $d['section'] ?? ''),
            'Detail roster ditambahkan.'
        );
    }

    public function updateDetail(SaveRosterDetailRequest $request, int $id, int $detail): RedirectResponse
    {
        return $this->done(fn () => $this->service->updateDetail($id, $detail, $request->validated()), 'Detail roster diperbarui.');
    }

    public function destroyDetail(ManageRosterRequest $request, int $id, int $detail): RedirectResponse
    {
        return $this->done(fn () => $this->service->deleteDetail($id, $detail), 'Detail dihapus.');
    }

    public function storeAllocation(SaveRosterAllocationRequest $request, int $id, int $detail): RedirectResponse
    {
        $d = $request->validated();

        return $this->done(fn () => $this->service->allocateDetail($id, $detail, $d['unit'] ?? '', $d['section'] ?? ''), 'Alokasi ditambahkan.');
    }

    public function destroyAllocation(SaveRosterAllocationRequest $request, int $id, int $detail): RedirectResponse
    {
        $d = $request->validated();

        return $this->done(fn () => $this->service->removeAllocation($id, $detail, $d['unit'] ?? '', $d['section'] ?? ''), 'Alokasi dihapus.');
    }

    // ------------------------------------------------------- Import & pencarian (JSON)

    public function searchEmployee(SearchRosterEmployeeRequest $request): JsonResponse
    {
        return $this->guardJson(function () use ($request) {
            if (! $request->has('nrp')) {
                return $this->service->searchEmployees((string) $request->query('q', ''));
            }

            return $this->service->lookupEmployee((string) $request->query('nrp')) ?? throw new RosterV2NotFound('NRP tidak ditemukan di data karyawan.');
        });
    }

    public function importPreview(PreviewRosterImportRequest $request, int $id): JsonResponse
    {
        return $this->guardJson(function () use ($request, $id) {
            $file = $request->file('file');
            $parsed = RosterV2Xlsx::readImportFile($file->getRealPath(), $file->getClientOriginalName());
            $report = $this->service->previewImport($id, $parsed['rows']);

            return [
                'token' => $this->service->stashImport($id, $report['importable']),
                'summary' => $report['summary'],
                'rows' => $report['rows'],
                'ignored_headers' => $parsed['ignored_headers'],
            ];
        });
    }

    public function importCommit(CommitRosterImportRequest $request, int $id): JsonResponse
    {
        return $this->guardJson(function () use ($request, $id) {
            $n = $this->service->commitImport($id, $request->validated('token'));

            return ['imported' => $n, 'message' => "$n baris berhasil diimport."];
        });
    }

    public function sheetPreview(PreviewRosterSheetRequest $request, RosterV2SheetImportService $import): JsonResponse
    {
        @set_time_limit(180);   // fetch sheet + jalankan impor bisa >30 dtk pada sheet besar
        $d = $request->validated();

        return $this->guardJson(fn () => $import->previewRoster((string) $d['url'], (int) $d['roster_id']));
    }

    public function sheetCommit(CommitRosterSheetRequest $request, RosterV2SheetImportService $import): JsonResponse
    {
        @set_time_limit(180);
        $d = $request->validated();

        return $this->guardJson(fn () => [...$import->commitRoster((int) $d['roster_id'], (string) $d['token'], $request->boolean('sync_moncu')), 'message' => 'Impor roster selesai.']);
    }

    // ------------------------------------------------------------ Pembantu

    /** Aksi Inertia: sukses -> kembali dengan flash; gagal domain -> error form `form`. */
    private function done(callable $action, string $message): RedirectResponse
    {
        return $this->guardPage(function () use ($action, $message) {
            $action();

            return back()->with('success', $message);
        });
    }

    private function layoutProps(): array
    {
        return [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_roster_menu'),
        ];
    }

    /** Nilai awal PIC Roster = user yang login. */
    private function me(): array
    {
        $user = Auth::user();

        return ['name' => (string) ($user->name ?: $user->username), 'jabatan' => (string) $user->jabatan];
    }

    private function changelog(int $id): ?array
    {
        try {
            $log = $this->service->getChangelog($id, 3);

            return [...$log, 'rows' => RosterChangelogResource::collection($log['rows'])->resolve()];
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private function listData(int $id, int $page): array
    {
        $grid = $this->service->getRosterGrid($id, $page, 100);

        return [
            'details' => RosterDetailResource::fromGrid($grid['details'], $grid['allocations']),
            'page' => $grid['page'],
            'per_page' => $grid['per_page'],
            'pages' => $grid['pages'],
            'total' => $grid['total'],
        ];
    }

    private function matrixData(ShowRosterRequest $request, int $id): ?array
    {
        $ctx = $request->text('ctx');   // p = 10 hari bulan sebelumnya, n = 10 hari bulan setelahnya

        try {
            $matrix = $this->service->getMatrix($id, $request->text('month') ?: null, ['prev' => str_contains($ctx, 'p'), 'next' => str_contains($ctx, 'n')]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        return (new RosterMatrixResource($matrix))->resolve();
    }

    private function uploadQuery(ShowRosterRequest $request): array
    {
        return [
            'month' => $request->text('month'), 'start' => $request->text('start'), 'end' => $request->text('end'),
            'pos' => $request->text('pos'), 'nrps' => $request->text('nrps'),
        ];
    }

    private function uploadData(ShowRosterRequest $request, int $id): ?array
    {
        try {
            $upload = $this->service->getUploadData($id, $this->uploadQuery($request));
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        return [...$upload, 'roster' => (new RosterResource($upload['roster']))->resolve()];
    }

    private function duplicateMessage(array $r): string
    {
        return 'Roster diduplikasi: '.$r['sections'].' section, '.$r['members'].' anggota, '.$r['cells'].' sel putaran dibuat.'
            .($r['moncu_new'] > 0 ? ' '.$r['moncu_new'].' orang baru didaftarkan ke MONCU.' : '')
            .($r['no_kode'] > 0 ? ' '.$r['no_kode'].' anggota tanpa Kode Off (jadwal kosong).' : '')
            .($r['skipped'] ? ' '.count($r['skipped']).' NRP dilewati (tidak ada di data karyawan).' : '');
    }

    private function rotationMessage(array $r, bool $apply): string
    {
        if (! $apply) {
            return 'Kode Off disimpan.';
        }

        return 'Putaran '.$r['kode_off'].($r['work_pattern'] === 'FULL_SIANG' ? ' (full siang)' : '').' diterapkan: '.$r['written'].' hari ditulis'
            .($r['kept'] ? ', '.$r['kept'].' hari dipertahankan (CT/ID/MCU/H/TR)' : '').'.';
    }

    private function syncMessage(array $r): string
    {
        $msg = "Sinkron cuti selesai: {$r['created']} dibuat, {$r['changed']} diubah, {$r['reverted']} dikembalikan.";
        $notInRoster = $r['not_in_roster'] ?? [];
        if ($notInRoster) {
            $msg .= ' '.count($notInRoster).' karyawan MONCU punya cuti pada periode ini tetapi belum ada di roster (dilewati, tidak ditambahkan): '
                .implode(', ', array_slice($notInRoster, 0, 10)).(count($notInRoster) > 10 ? ', ...' : '').'.';
        }
        if (! empty($r['kept'])) {
            $msg .= ' '.$r['kept'].' sel dipertahankan karena sudah punya alokasi unit.';
        }

        return $msg;
    }

    /** Pesan setelah tambah anggota; bila putaran terisi, cuti MONCU ikut diterapkan (kegagalan sinkron tidak menggagalkan penambahan). */
    private function memberAddedMessage(MoncuService $moncu, int $rosterId, string $nrp): string
    {
        $msg = 'Anggota ditambahkan ke section.'.($this->service->lastMemberMoncuCreated ? ' Otomatis didaftarkan di MONCU (Hiring Date belum diisi).' : '');
        $cells = $this->service->lastMemberRotationCells;
        if ($cells < 1) {
            return $msg;
        }
        $msg .= ' Putaran dari Kode Off terisi '.$cells.' hari.';
        try {
            $leave = $moncu->syncRosterV2LeaveStatus($nrp, $rosterId);
            $n = (int) $leave['created'] + (int) $leave['changed'];
            $msg .= $n > 0 ? ' Cuti dari MONCU diterapkan ('.$n.' sel).' : '';
        } catch (Throwable $e) {
            report($e);
        }

        return $msg;
    }
}
