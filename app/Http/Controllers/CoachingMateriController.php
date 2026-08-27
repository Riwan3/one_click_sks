<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachingMateriRequest;
use App\Http\Resources\CoachingMateriResource;
use App\Models\CoachingMateri;
use App\Services\CoachingMateriService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CoachingMateriController extends Controller
{
    public function __construct(private CoachingMateriService $service) {}

    public function index()
    {
        $materis = CoachingMateri::query()
            ->with('revisis')
            ->orderBy('kategori')
            ->paginate(12);

        return Inertia::render('CoachingMateri/Index', [
            'materis' => CoachingMateriResource::collection($materis),
        ]);
    }

    public function create()
    {
        return Inertia::render('CoachingMateri/Create');
    }

    public function store(StoreCoachingMateriRequest $request)
    {
        $this->service->create($request->validated(), $request->user()->id);

        return redirect()->route('coaching-materi.index')
            ->with('success', 'Materi coaching berhasil ditambahkan.');
    }

    public function show(CoachingMateri $coachingMateri)
    {
        $coachingMateri->load('revisis');

        return Inertia::render('CoachingMateri/Show', [
            'materi' => new CoachingMateriResource($coachingMateri),
        ]);
    }

    public function edit(CoachingMateri $coachingMateri)
    {
        return Inertia::render('CoachingMateri/Edit', [
            'materi' => new CoachingMateriResource($coachingMateri),
        ]);
    }

    public function update(StoreCoachingMateriRequest $request, CoachingMateri $coachingMateri)
    {
        $isRevisi = $request->boolean('is_revisi', false);

        $this->service->update($coachingMateri, $request->validated(), $request->user()->id, $isRevisi);

        return redirect()->route('coaching-materi.index')
            ->with('success', 'Materi coaching berhasil diperbarui.');
    }

    public function destroy(CoachingMateri $coachingMateri)
    {
        $coachingMateri->delete();

        return redirect()->route('coaching-materi.index')
            ->with('success', 'Materi coaching berhasil dihapus.');
    }
}
