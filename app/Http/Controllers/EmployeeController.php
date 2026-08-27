<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeStatus;
use App\Exports\EmployeeExport;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Imports\EmployeeImport;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(private EmployeeService $employeeService) {}

    public function index(Request $request): Response
    {
        return $this->renderList($request, onlyInactive: false);
    }

    public function inactive(Request $request): Response
    {
        return $this->renderList($request, onlyInactive: true);
    }

    public function create(): Response
    {
        return Inertia::render('OnePower/Employees/Create', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->employeeService->create($request->validated());

        return redirect('/one-power/employees')->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('OnePower/Employees/Edit', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'statuses' => $this->statusOptions(),
            'targetEmployee' => new EmployeeResource($employee),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update($employee, $request->validated());

        return redirect('/one-power/employees')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->employeeService->delete($employee);

        return redirect()->back()->with('success', 'Karyawan berhasil dihapus.');
    }

    public function setStatus(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'status' => ['required', new Enum(EmployeeStatus::class)],
        ]);

        $this->employeeService->setStatus($employee, $request->string('status')->toString());

        $label = $request->input('status') === EmployeeStatus::Inactive->value ? 'dinonaktifkan' : 'diaktifkan';

        return redirect()->back()->with('success', "Karyawan berhasil {$label}.");
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new EmployeeImport;
        Excel::import($import, $request->file('file'));

        return redirect()->back()->with('success',
            "Impor selesai: {$import->createdCount()} data baru ditambahkan, {$import->updatedCount()} data diperbarui. Data lama tidak dihapus."
        );
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'NO', 'NRP', 'PASSWORD', 'NAMA', 'JABATAN SS6', 'JABATAN', 'STATUS KARYAWAN',
            'LOKASI KERJA', 'UNIT', 'NO TELEPON', 'EFEKTIF BERKERJA DI SITE ADARO INDONESIA',
            'INPUT HERE!!!', 'EMAIL',
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            fputcsv($handle, [1, '260110', '260110', 'CONTOH NAMA KARYAWAN', 'OPERATOR', '1B', 'FIT', 'PIT 1', 'EX2600', '081234567890', '01/01/2020', '', 'contoh@ppa.co.id']);
            fputcsv($handle, [2, '260111', '260111', 'CONTOH NAMA KARYAWAN 2', 'GROUP LEADER', '2A', 'FIT', 'PIT 2', 'HD785', '081234567891', '15/08/2021', '', '']);
            fclose($handle);
        }, 'contoh_import_karyawan.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function export(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $jabatan = trim((string) $request->string('jabatan'));
        $onlyInactive = $request->boolean('only_inactive');

        $employees = $this->employeeService->forExport($search ?: null, $jabatan ?: null, $onlyInactive);

        return Excel::download(new EmployeeExport($employees), 'export_karyawan_'.now()->format('Y-m-d').'.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function bulkDeleteInactive(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $count = $this->employeeService->deleteAllInactive();

        return redirect('/one-power/employees/inactive')->with('success', "{$count} karyawan non-aktif berhasil dihapus.");
    }

    public function listGL(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $gls = $this->employeeService->listAllGL($search ?: null);

        return Inertia::render('OnePower/Employees/ListGl', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'gls' => EmployeeResource::collection($gls)->resolve(),
        ]);
    }

    public function anakAsuh(Request $request, Employee $gl): Response
    {
        $search = trim((string) $request->string('search'));

        $mienteed = $this->employeeService->getAnakAsuh($gl, $search ?: null);

        return Inertia::render('OnePower/Employees/AnakAsuh', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'gl' => new EmployeeResource($gl),
            'search' => $search,
            'employees' => EmployeeResource::collection($mienteed)->resolve(),
        ]);
    }

    public function selectAnakAsuh(Request $request, Employee $gl): Response
    {
        $search = trim((string) $request->string('search'));

        $unassigned = $this->employeeService->getUnassignedEmployees($search ?: null);

        return Inertia::render('OnePower/Employees/SelectAnakAsuh', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'gl' => new EmployeeResource($gl),
            'search' => $search,
            'unassignedEmployees' => EmployeeResource::collection($unassigned)->resolve(),
        ]);
    }

    public function bulkStoreAsuh(Request $request): RedirectResponse
    {
        $request->validate([
            'gl_id' => ['required', 'exists:employees,id'],
            'employee_ids' => ['required', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);

        $gl = Employee::findOrFail($request->integer('gl_id'));
        $count = $this->employeeService->bulkAssignAnakAsuh($gl, $request->input('employee_ids'));

        return redirect("/one-power/employees/{$gl->id}/anak-asuh")->with('success', "{$count} anak asuh berhasil ditambahkan ke GL {$gl->name}.");
    }

    public function importAsuh(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'reset_lama' => ['nullable', 'boolean'],
        ]);

        $result = $this->employeeService->importSahabatPengusaha(
            $request->file('file'),
            $request->boolean('reset_lama')
        );

        return redirect('/one-power/employees/list-gl')->with('success', $result['message']);
    }

    public function setAsuh(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'gl_id' => ['nullable', 'exists:employees,id'],
        ]);

        $this->employeeService->setGroupLeader($employee, $request->input('gl_id'));

        return redirect()->back()->with('success', 'Group Leader (Bapak Asuh) berhasil diperbarui.');
    }

    public function removeAsuh(Employee $employee): RedirectResponse
    {
        $this->employeeService->removeAsuh($employee);

        return redirect()->back()->with('success', 'Karyawan berhasil dilepas dari daftar anak asuh.');
    }

    public function print(Request $request, Employee $employee): Response
    {
        $tahun = max(2000, (int) $request->integer('tahun', (int) now()->format('Y')));

        // Load monthly ATR breakdown for employee
        $atrRows = \App\Models\AttendanceV2Detail::query()
            ->where('nrp', $employee->nrp)
            ->whereYear('tanggal', $tahun)
            ->get();

        $monthlyAtr = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthRows = $atrRows->filter(fn ($r) => (int) $r->tanggal->format('m') === $m);
            $d = $monthRows->where('sia_category', 'D')->count();
            $n = $monthRows->where('sia_category', 'N')->count();
            $s = $monthRows->where('sia_category', 'S')->count();
            $i = $monthRows->where('sia_category', 'I')->count();
            $a = $monthRows->where('sia_category', 'A')->count();
            $total = $d + $n + $s + $i + $a;
            $atr = $total > 0 ? round((($d + $n) / $total) * 100, 1) : 0;

            $monthlyAtr[] = [
                'bulan' => $m,
                'namaBulan' => date('F', mktime(0, 0, 0, $m, 1)),
                'd' => $d, 'n' => $n, 's' => $s, 'i' => $i, 'a' => $a,
                'total' => $total, 'atr' => $atr,
            ];
        }

        // Load monthly HM breakdown
        $hmRows = \App\Models\HmData::query()
            ->where('employee_id', $employee->id)
            ->whereYear('tanggal', $tahun)
            ->get();

        $monthlyHm = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthHm = $hmRows->filter(fn ($r) => (int) $r->tanggal->format('m') === $m);
            $sumHm = round((float) $monthHm->sum('hm_diff'), 1);
            $countShift = $monthHm->count();

            $monthlyHm[] = [
                'bulan' => $m,
                'namaBulan' => date('F', mktime(0, 0, 0, $m, 1)),
                'totalHm' => $sumHm,
                'totalShift' => $countShift,
            ];
        }

        // Load SP / Teguran history
        $spHistory = \App\Models\SuratPeringatan::query()->where('employee_id', $employee->id)->get();
        $teguranHistory = \App\Models\SuratTeguran::query()->where('employee_id', $employee->id)->get();
        $coachingHistory = \App\Models\CoachingClinic::query()->where('employee_id', $employee->id)->get();

        return Inertia::render('OnePower/Employees/Print', [
            'user' => $this->currentUser(),
            'employee' => (new EmployeeResource($employee->load(['groupLeader', 'anakAsuh'])))->resolve(),
            'tahun' => $tahun,
            'monthlyAtr' => $monthlyAtr,
            'monthlyHm' => $monthlyHm,
            'spHistory' => $spHistory,
            'teguranHistory' => $teguranHistory,
            'coachingHistory' => $coachingHistory,
        ]);
    }

    private function renderList(Request $request, bool $onlyInactive): Response
    {
        $search = trim((string) $request->string('search'));
        $jabatan = trim((string) $request->string('jabatan'));
        $page = max(1, (int) $request->integer('page', 1));

        $employees = $this->employeeService->paginate($search, $jabatan ?: null, $onlyInactive, $page, self::PER_PAGE);

        return Inertia::render('OnePower/Employees/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_power_menu'),
            'search' => $search,
            'jabatan' => $jabatan,
            'jabatanOptions' => $this->employeeService->jabatanOptions(),
            'onlyInactive' => $onlyInactive,
            'employees' => EmployeeResource::collection($employees->items())->resolve(),
            'pagination' => [
                'currentPage' => $employees->currentPage(),
                'lastPage' => $employees->lastPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    private function statusOptions(): array
    {
        return array_map(
            fn (EmployeeStatus $status) => ['value' => $status->value, 'label' => $status->label()],
            EmployeeStatus::cases()
        );
    }
}
