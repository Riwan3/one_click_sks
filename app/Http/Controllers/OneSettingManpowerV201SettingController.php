<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\ManpowerV201InputRequest;
use App\Http\Requests\ManpowerV201Request;
use App\Services\ManpowerFinalNotifier;
use App\Services\ManpowerV201SettingService;
use Illuminate\Http\RedirectResponse;

/** Aksi tulis Setting Harian Manpower v2.01 (port api/manpower-v201/setting.php). Aturan bisnis di Service. */
class OneSettingManpowerV201SettingController extends Controller
{
    use HandlesMoncuErrors;

    public function __construct(private ManpowerV201SettingService $setting) {}

    public function store(ManpowerV201InputRequest $request): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->create($request->text('date'), $request->text('shift')));

        return back()->with('success', 'Setting dibuat dan ditarik dari Roster V2.');
    }

    public function sync(ManpowerV201Request $request, int $setting): RedirectResponse
    {
        $result = $this->guardPage(fn () => $this->setting->sync($setting));
        $doubles = $result['doubles'] ? ', '.$result['doubles'].' unit double perlu dipilih.' : '.';

        return back()->with('success', 'Ditarik ulang: '.$result['placed'].' batangan terpasang'.$doubles);
    }

    public function finalize(ManpowerV201Request $request, int $setting): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->finalize($setting));
        $this->notifyOperators($setting);

        return back()->with('success', 'Setting difinalkan.');
    }

    /**
     * Kirim notifikasi push penempatan ke operator setelah respons terkirim (tidak menahan klik
     * "Finalkan"; galat pengiriman hanya dilaporkan ke log, tidak menggagalkan finalisasi).
     */
    private function notifyOperators(int $settingId): void
    {
        dispatch(function () use ($settingId) {
            try {
                app(ManpowerFinalNotifier::class)->notify($settingId);
            } catch (\Throwable $e) {
                report($e);
            }
        })->afterResponse();
    }

    public function reopen(ManpowerV201Request $request, int $setting): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->reopen($setting));

        return back()->with('success', 'Final dibuka, setting bisa diubah lagi.');
    }

    public function assign(ManpowerV201InputRequest $request, int $setting, int $unit): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->assign($setting, $unit, $request->text('nrp')));

        return back()->with('success', 'Orang ditempatkan.');
    }

    public function unassign(ManpowerV201Request $request, int $setting, int $unit): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->unassign($setting, $unit));

        return back()->with('success', 'Orang dilepas dari unit.');
    }

    public function resolveDouble(ManpowerV201InputRequest $request, int $setting, int $unit): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->resolveDouble($setting, $unit, $request->text('nrp')));

        return back()->with('success', 'Double diselesaikan.');
    }

    public function addLain(ManpowerV201InputRequest $request, int $setting): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->moveToLain($setting, $request->text('nrp'), $request->text('label')));

        return back()->with('success', 'Dipindahkan ke Lain-lain.');
    }

    public function removeLain(ManpowerV201InputRequest $request, int $setting): RedirectResponse
    {
        $this->guardPage(fn () => $this->setting->removeFromLain($setting, $request->text('nrp')));

        return back()->with('success', 'Dihapus dari Lain-lain.');
    }
}
