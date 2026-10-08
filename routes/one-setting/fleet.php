<?php

use App\Http\Controllers\OneSettingFleetController;
use App\Http\Controllers\OneSettingFleetMasterController;
use App\Http\Controllers\OneSettingFleetSettingController;
use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. Akses: Admin, Admin Produksi, GL Manpower (Form Request).
Route::get('/one-setting/fleet', [OneSettingFleetController::class, 'index'])->name('one-setting.fleet.index');

Route::prefix('one-setting/fleet')->name('one-setting.fleet.')->whereNumber('unit')->group(function () {
    Route::get('master', [OneSettingFleetMasterController::class, 'index'])->name('master');

    Route::controller(OneSettingFleetMasterController::class)->prefix('master')->name('master.')->group(function () {
        Route::post('units', 'store')->name('units.store');
        Route::put('units/{unit}', 'update')->name('units.update');
        Route::put('units/{unit}/active', 'active')->name('units.active');
        Route::delete('units/{unit}', 'destroy')->name('units.destroy');
        Route::post('pull', 'pull')->name('pull');
    });

    Route::controller(OneSettingFleetSettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::post('loader', 'storeLoader')->name('loader.store');
        Route::put('loader', 'updateLoader')->name('loader.update');
        Route::delete('loader', 'destroyLoader')->name('loader.destroy');
        Route::post('hauler', 'storeHauler')->name('hauler.store');
        Route::put('hauler', 'updateHauler')->name('hauler.update');
        Route::post('hauler/move', 'moveHauler')->name('hauler.move');
        Route::delete('hauler', 'destroyHauler')->name('hauler.destroy');
        Route::post('copy', 'copy')->name('copy');
        Route::delete('clear', 'clear')->name('clear');
    });
});
