<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeStatus;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

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
            'menu' => config('one_power_menu'),
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
            'menu' => config('one_power_menu'),
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

    private function renderList(Request $request, bool $onlyInactive): Response
    {
        $search = trim((string) $request->string('search'));
        $jabatan = trim((string) $request->string('jabatan'));
        $page = max(1, (int) $request->integer('page', 1));

        $employees = $this->employeeService->paginate($search, $jabatan ?: null, $onlyInactive, $page, self::PER_PAGE);

        return Inertia::render('OnePower/Employees/Index', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => config('one_power_menu'),
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

    private function notifCounts(): array
    {
        return ['signature' => 2, 'notification' => 3, 'message' => 1];
    }
}
