<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportMinimalGradeRequest;
use App\Http\Requests\SaveMinimalGradeRequest;
use App\Http\Resources\OpdMinimalGradeResource;
use App\Models\OpdMinimalGrade;
use App\Services\MinimalGradeService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OpdMinimalGradeController extends Controller
{
    public function __construct(private MinimalGradeService $service) {}

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        return Inertia::render('OneAcademy/Opd/MinimalGrade', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_academy_menu'),
            'search' => $search,
            'rows' => OpdMinimalGradeResource::collection($this->service->list($search))->resolve(),
        ]);
    }

    public function store(SaveMinimalGradeRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $this->username());

        return back()->with('success', 'Data minimal grade berhasil disimpan.');
    }

    public function update(SaveMinimalGradeRequest $request, OpdMinimalGrade $minimalGrade): RedirectResponse
    {
        $this->service->save($request->validated(), $this->username(), $minimalGrade);

        return back()->with('success', 'Data minimal grade berhasil disimpan.');
    }

    public function destroy(OpdMinimalGrade $minimalGrade): RedirectResponse
    {
        $minimalGrade->delete();

        return back()->with('success', 'Data minimal grade berhasil dihapus.');
    }

    public function import(ImportMinimalGradeRequest $request): RedirectResponse
    {
        try {
            $result = $this->service->import(
                $request->file('file_sheet'),
                $request->validated('sheet_url'),
                $request->boolean('replace_all'),
                $this->username(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $suffix = $request->boolean('replace_all') ? ' (data lama diganti)' : '';

        return back()->with('success', "Import selesai: {$result['inserted']} data baru, {$result['updated']} data diperbarui{$suffix}.");
    }

    private function username(): string
    {
        return (string) Auth::user()->username;
    }
}
