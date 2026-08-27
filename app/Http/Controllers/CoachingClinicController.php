<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachingClinicRequest;
use App\Http\Resources\CoachingClinicResource;
use App\Models\CoachingClinic;
use App\Models\Employee;
use App\Services\CoachingClinicService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingClinicController extends Controller
{
    public function __construct(private CoachingClinicService $service) {}

    public function index(Request $request)
    {
        abort_if($request->user()?->isManagement() ?? false, 403);

        $clinics = $this->service->paginate(15);

        return Inertia::render('CoachingClinic/Index', [
            'clinics' => CoachingClinicResource::collection($clinics),
            'canManage' => ! ($request->user()?->isManagement() ?? false),
        ]);
    }

    public function create()
    {
        abort_if(auth()->user()?->isManagement() ?? false, 403);

        return Inertia::render('CoachingClinic/Create', [
            'status' => 'draft',
        ]);
    }

    public function store(StoreCoachingClinicRequest $request)
    {
        abort_if($request->user()?->isManagement() ?? false, 403);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] = $request->input('status', 'draft');

        $this->service->create($data);

        return redirect()->route('coaching-clinic.index')
            ->with('success', 'Coaching clinic berhasil ditambahkan.');
    }

    public function show(CoachingClinic $coachingClinic)
    {
        $coachingClinic->load(['employee', 'coach', 'creator']);

        return Inertia::render('CoachingClinic/Show', [
            'clinic' => new CoachingClinicResource($coachingClinic),
        ]);
    }

    public function edit(CoachingClinic $coachingClinic)
    {
        abort_if(auth()->user()?->isManagement() ?? false, 403);

        $coachingClinic->load(['employee', 'coach']);

        return Inertia::render('CoachingClinic/Edit', [
            'clinic' => new CoachingClinicResource($coachingClinic),
        ]);
    }

    public function update(StoreCoachingClinicRequest $request, CoachingClinic $coachingClinic)
    {
        abort_if($request->user()?->isManagement() ?? false, 403);

        $this->service->update($coachingClinic, $request->validated());

        return redirect()->route('coaching-clinic.index')
            ->with('success', 'Coaching clinic berhasil diperbarui.');
    }

    public function destroy(Request $request, CoachingClinic $coachingClinic)
    {
        abort_if($request->user()?->isManagement() ?? false, 403);

        $this->service->delete($coachingClinic);

        return redirect()->route('coaching-clinic.index')
            ->with('success', 'Coaching clinic berhasil dihapus.');
    }

    public function searchEmployee(Request $request)
    {
        $search = $request->query('q', '');

        $employees = Employee::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nrp', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get(['id', 'nrp', 'nama', 'departemen']);

        return response()->json($employees);
    }
}
