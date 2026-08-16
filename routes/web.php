<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\OneAcademyController;
use App\Http\Controllers\OneAdminController;
use App\Http\Controllers\OneCoreController;
use App\Http\Controllers\OneDmsController;
use App\Http\Controllers\OneGmpController;
use App\Http\Controllers\OnePowerController;
use App\Http\Controllers\OneProcedureController;
use App\Http\Controllers\OneReportingController;
use App\Http\Controllers\OneSettingController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return session('dummy_user') ? redirect('/portal') : redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/portal', [PortalController::class, 'index'])->name('portal');
Route::get('/portal/data', [PortalController::class, 'mobileData'])->name('mobile.data');
Route::get('/portal/aktivity', [PortalController::class, 'aktivity'])->name('mobile.aktivity');
Route::get('/portal/profil', [PortalController::class, 'profil'])->name('mobile.profil');

Route::get('/one-power', [OnePowerController::class, 'dashboard'])->name('one-power.dashboard');
Route::get('/one-academy', [OneAcademyController::class, 'dashboard'])->name('one-academy.dashboard');

Route::get('/one-core', [OneCoreController::class, 'dashboard'])->name('one-core.dashboard');
Route::post('/one-core/unlock', [OneCoreController::class, 'unlock'])->name('one-core.unlock');
Route::post('/one-core/lock', [OneCoreController::class, 'lock'])->name('one-core.lock');

Route::get('/one-dms', [OneDmsController::class, 'dashboard'])->name('one-dms.dashboard');

Route::get('/one-gmp', [OneGmpController::class, 'dashboard'])->name('one-gmp.dashboard');

Route::get('/one-reporting', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard');

Route::get('/one-setting', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard');

Route::get('/one-admin', [OneAdminController::class, 'dashboard'])->name('one-admin.dashboard');

Route::get('/one-procedure', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard');

$implementedModuleKeys = ['one-power', 'one-academy', 'one-core', 'one-dms', 'one-gmp', 'one-reporting', 'one-setting', 'one-admin', 'one-procedure'];

$moduleKeys = collect(config('modules.list'))
    ->pluck('key')
    ->reject(fn (string $key) => in_array($key, $implementedModuleKeys, true))
    ->implode('|');

Route::get('/{module}', [ModuleController::class, 'show'])
    ->where('module', $moduleKeys)
    ->name('module.show');
