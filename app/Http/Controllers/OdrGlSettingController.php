<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParseOdrGlSettingRequest;
use App\Http\Resources\OdrGlSettingResource;
use App\Models\OdrAssignment;
use App\Models\OdrGlSetting;
use App\Services\OdrGlSettingService;
use App\Support\MenuAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Setting GL One-Reporting (padanan database/odr/setting_gl.php v1). */
class OdrGlSettingController extends Controller
{
    public function __construct(private OdrGlSettingService $glSettingService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('manage-gl-setting');

        $tanggal = strtotime((string) $request->query('tanggal')) ? Carbon::parse($request->query('tanggal'))->toDateString() : now()->toDateString();
        $shift = trim((string) $request->query('shift', '')) ?: 'Shift 1';

        return Inertia::render('OneReporting/SettingGl', [
            'user' => $this->currentUser(),
            'notifCounts' => $this->notifCounts(),
            'menu' => MenuAccess::forConfig('one_reporting_menu'),
            'filters' => ['tanggal' => $tanggal, 'shift' => $shift],
            'rows' => OdrGlSettingResource::collection($this->glSettingService->rows($tanggal, $shift))->resolve(),
            'history' => $this->glSettingService->history(),
            'shiftOptions' => OdrAssignment::SHIFTS,
        ]);
    }

    public function parse(ParseOdrGlSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();
        $shift = trim($data['shift']);
        $back = redirect()->route('one-reporting.setting-gl', ['tanggal' => $tanggal, 'shift' => $shift]);

        $result = $this->glSettingService->parseAndSave($tanggal, $shift, (string) ($data['front_text'] ?? ''), (string) ($data['disposal_text'] ?? ''));
        if (! $result) {
            return $back->with('error', 'Tidak ada data yang berhasil diparsing. Pastikan kolom dipisahkan dengan Tab (hasil copy-paste dari Excel).');
        }

        return $back->with('success', sprintf(
            '%d baris disimpan untuk %s - %s — Front PIC: %d, Disposal PIC: %d. %d assignment disinkronkan ke Daily Assignment.',
            $result['rows'], Carbon::parse($tanggal)->format('d/m/Y'), $shift, $result['front'], $result['disposal'], $result['assignments'],
        ));
    }

    public function destroy(OdrGlSetting $setting): RedirectResponse
    {
        Gate::authorize('manage-gl-setting');

        $setting->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function destroyBatch(Request $request): RedirectResponse
    {
        Gate::authorize('manage-gl-setting');
        $data = $request->validate(['tanggal' => ['required', 'date'], 'shift' => ['required', 'string']]);
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        $this->glSettingService->deleteBatch($tanggal, $data['shift']);

        return back()->with('success', sprintf(
            'Semua data tanggal %s - %s beserta assignment hasil sync-nya berhasil dihapus.',
            Carbon::parse($tanggal)->format('d/m/Y'), $data['shift'],
        ));
    }
}
