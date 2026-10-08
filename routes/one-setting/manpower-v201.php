<?php

use App\Http\Controllers\OneSettingManpowerV201Controller;
use App\Http\Controllers\OneSettingManpowerV201MasterController;
use App\Http\Controllers\OneSettingManpowerV201SettingController;
use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. Akses: Admin, Admin Produksi, GL Manpower (Form Request).
Route::prefix('one-setting/manpower-v201')->name('one-setting.manpower-v201.')->whereNumber(['setting', 'unit', 'csa', 'category'])->group(function () {
    // Halaman
    Route::get('/', [OneSettingManpowerV201Controller::class, 'index'])->name('index');
    Route::get('preview', [OneSettingManpowerV201Controller::class, 'preview'])->name('preview');
    Route::get('master', [OneSettingManpowerV201Controller::class, 'master'])->name('master');
    Route::get('monitoring', [OneSettingManpowerV201Controller::class, 'monitoring'])->name('monitoring');
    Route::get('monitoring-mp', [OneSettingManpowerV201Controller::class, 'monitoringMp'])->name('monitoring-mp');
    Route::get('candidates', [OneSettingManpowerV201Controller::class, 'candidates'])->name('candidates');

    // Setting harian
    Route::controller(OneSettingManpowerV201SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('{setting}/sync', 'sync')->name('sync');
        Route::post('{setting}/finalize', 'finalize')->name('finalize');
        Route::post('{setting}/reopen', 'reopen')->name('reopen');
        Route::post('{setting}/lain', 'addLain')->name('lain.store');
        Route::delete('{setting}/lain', 'removeLain')->name('lain.destroy');
        Route::post('{setting}/units/{unit}/assign', 'assign')->name('assign');
        Route::post('{setting}/units/{unit}/double', 'resolveDouble')->name('double');
        Route::delete('{setting}/units/{unit}/assign', 'unassign')->name('unassign');
    });

    // Master CSA
    Route::controller(OneSettingManpowerV201MasterController::class)->prefix('master')->name('master.')->group(function () {
        Route::post('csa', 'storeCsa')->name('csa.store');
        Route::put('csa/{csa}', 'updateCsa')->name('csa.update');
        Route::put('csa/{csa}/lock', 'lockCsa')->name('csa.lock');
        Route::post('csa/{csa}/move', 'moveCsa')->name('csa.move');
        Route::delete('csa/{csa}', 'destroyCsa')->name('csa.destroy');

        Route::post('categories', 'storeCategory')->name('categories.store');
        Route::put('categories/{category}', 'updateCategory')->name('categories.update');
        Route::post('categories/{category}/move', 'moveCategory')->name('categories.move');
        Route::delete('categories/{category}', 'destroyCategory')->name('categories.destroy');

        Route::post('units', 'storeUnits')->name('units.store');
        Route::put('units/{unit}', 'updateUnit')->name('units.update');
        Route::put('units/{unit}/risk', 'riskUnit')->name('units.risk');
        Route::put('units/{unit}/category', 'moveUnitToCategory')->name('units.category');
        Route::post('units/{unit}/move', 'moveUnit')->name('units.move');
        Route::delete('units/{unit}', 'destroyUnit')->name('units.destroy');
    });
});
