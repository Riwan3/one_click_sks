<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesMoncuErrors;
use App\Http\Requests\ManpowerV201InputRequest;
use App\Http\Requests\ManpowerV201Request;
use App\Services\ManpowerV201Service;
use Illuminate\Http\RedirectResponse;

/** Aksi tulis Master CSA Setting Manpower v2.01 (port api/manpower-v201/master.php). Aturan bisnis di Service. */
class OneSettingManpowerV201MasterController extends Controller
{
    use HandlesMoncuErrors;

    public function __construct(private ManpowerV201Service $master) {}

    public function storeCsa(ManpowerV201InputRequest $request): RedirectResponse
    {
        return $this->done(fn () => $this->master->addCsa($request->text('name')), 'CSA ditambahkan.');
    }

    public function updateCsa(ManpowerV201InputRequest $request, int $csa): RedirectResponse
    {
        return $this->done(fn () => $this->master->renameCsa($csa, $request->text('name')));
    }

    public function lockCsa(ManpowerV201Request $request, int $csa): RedirectResponse
    {
        $locked = $request->boolean('locked');

        return $this->done(fn () => $this->master->setCsaLocked($csa, $locked), $locked ? 'CSA dikunci.' : 'Kunci CSA dibuka.');
    }

    public function destroyCsa(ManpowerV201Request $request, int $csa): RedirectResponse
    {
        return $this->done(fn () => $this->master->deleteCsa($csa), 'CSA dihapus.');
    }

    public function moveCsa(ManpowerV201InputRequest $request, int $csa): RedirectResponse
    {
        return $this->done(fn () => $this->master->moveCsa($csa, $request->text('dir')));
    }

    public function storeCategory(ManpowerV201InputRequest $request): RedirectResponse
    {
        return $this->done(
            fn () => $this->master->addCategory($request->requiredId('csa_id'), $request->text('name'), $request->input('col_pos', 1)),
            'Kategori ditambahkan.',
        );
    }

    public function updateCategory(ManpowerV201InputRequest $request, int $category): RedirectResponse
    {
        return $this->done(fn () => $this->master->updateCategory($category, $request->text('name'), $request->input('col_pos', 1)));
    }

    public function destroyCategory(ManpowerV201Request $request, int $category): RedirectResponse
    {
        return $this->done(fn () => $this->master->deleteCategory($category), 'Kategori dihapus.');
    }

    public function moveCategory(ManpowerV201InputRequest $request, int $category): RedirectResponse
    {
        return $this->done(fn () => $this->master->moveCategory($category, $request->text('dir')));
    }

    public function storeUnits(ManpowerV201InputRequest $request): RedirectResponse
    {
        $result = $this->guardPage(fn () => $this->master->addUnits(
            $request->requiredId('category_id'), $request->text('units'), $request->boolean('high_risk'),
        ));
        $skipped = $result['skipped'];
        $message = count($result['added']).' unit ditambahkan'.($skipped ? ', '.count($skipped).' dilewati (sudah terdaftar): '.implode('; ', $skipped).'.' : '.');

        return back()->with('success', $message);
    }

    public function updateUnit(ManpowerV201InputRequest $request, int $unit): RedirectResponse
    {
        return $this->done(fn () => $this->master->renameUnit($unit, $request->text('unit')));
    }

    public function riskUnit(ManpowerV201Request $request, int $unit): RedirectResponse
    {
        return $this->done(fn () => $this->master->setUnitHighRisk($unit, $request->boolean('high_risk')));
    }

    public function destroyUnit(ManpowerV201Request $request, int $unit): RedirectResponse
    {
        return $this->done(fn () => $this->master->deleteUnit($unit), 'Unit dihapus.');
    }

    public function moveUnit(ManpowerV201InputRequest $request, int $unit): RedirectResponse
    {
        return $this->done(fn () => $this->master->moveUnit($unit, $request->text('dir')));
    }

    public function moveUnitToCategory(ManpowerV201Request $request, int $unit): RedirectResponse
    {
        return $this->done(fn () => $this->master->moveUnitToCategory($unit, $request->requiredId('category_id')), 'Unit dipindahkan.');
    }

    private function done(callable $action, string $message = 'Tersimpan.'): RedirectResponse
    {
        $this->guardPage($action);

        return back()->with('success', $message);
    }
}
