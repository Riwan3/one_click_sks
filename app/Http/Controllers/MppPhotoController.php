<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMppPhotoRequest;
use App\Http\Resources\MppPhotoResource;
use App\Models\MppUnitPhoto;
use App\Services\MppPhotoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MppPhotoController extends Controller
{
    public function __construct(private MppPhotoService $service) {}

    public function create()
    {
        abort_if(! in_array(auth()->user()->role, ['admin', 'pimpinan', 'gl_manpower', 'operator']), 403);

        return Inertia::render('MppDepartemen/PhotoUpload', [
            'units' => ['UNIT001', 'UNIT002', 'UNIT003'], // TODO: fetch from database
        ]);
    }

    public function store(StoreMppPhotoRequest $request)
    {
        $validated = $request->validated();
        $photo = $this->service->store($validated);

        return redirect()->back()
            ->with('success', 'Foto berhasil diunggah.')
            ->with('photo_url', $photo->url);
    }

    public function showByJenis(string $jenis, Request $request)
    {
        abort_if(! in_array($jenis, ['OJK', 'Checkpoint']), 404);

        $photos = MppUnitPhoto::byJenis($jenis)
            ->latest()
            ->paginate(20);

        return Inertia::render('MppDepartemen/PhotoArchive', [
            'jenis' => $jenis,
            'photos' => MppPhotoResource::collection($photos),
            'pagination' => [
                'total' => $photos->total(),
                'per_page' => $photos->perPage(),
                'current_page' => $photos->currentPage(),
                'last_page' => $photos->lastPage(),
            ],
        ]);
    }
}
