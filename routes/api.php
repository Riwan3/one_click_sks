<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Api\AssessmentApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ControlCenterController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\MorRaportController;
use App\Http\Controllers\Api\PengajuanGradeController;
use App\Http\Controllers\Api\QorApiController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CoachingCounselingController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HmDataController;
use App\Http\Controllers\IzinKaryawanController;
use App\Http\Controllers\NomorPentingController;
use App\Http\Controllers\OdrDailyComplianceController;
use App\Http\Controllers\OneGmpController;
use App\Http\Controllers\OneReportingController;
use App\Http\Controllers\OneSettingController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RosterPersonalController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);

// Dipanggil OneClick Control Center (bearer token CONTROL_CENTER_TOKEN)
Route::middleware(['cc.token', 'throttle:60,1'])->prefix('cc')->group(function () {
    Route::get('/status', [ControlCenterController::class, 'status']);
    Route::get('/activity', [ControlCenterController::class, 'activity']);
    Route::get('/menus', [ControlCenterController::class, 'menus']);
});

// Dipanggil OneClick HO untuk mengintip Pengajuan Grade & mengisi Remark (token PENGAJUAN_API_TOKEN).
// Alias .php menjaga kompatibilitas dengan alamat lama (api/pengajuan-grade.php) yang dipakai HO.
Route::middleware(['pengajuan.token', 'throttle:120,1'])
    ->match(['get', 'post'], '/pengajuan-grade{legacy?}', PengajuanGradeController::class)
    ->where('legacy', '(\.php)?')
    ->name('api.pengajuan-grade');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
    Route::get('/push-setting', [DeviceTokenController::class, 'setting']);
    Route::post('/push-setting', [DeviceTokenController::class, 'updateSetting']);
    Route::get('/pengumuman/active', [AnnouncementController::class, 'active']);
    Route::get('/home', [PortalController::class, 'homeApi']);
    Route::get('/data', [PortalController::class, 'dataApi']);
    Route::get('/aktivity', [PortalController::class, 'aktivityApi']);
    Route::get('/profil', [PortalController::class, 'profilApi']);
    Route::post('/profil/foto', [PortalController::class, 'updateFotoApi']);
    Route::delete('/profil/foto', [PortalController::class, 'destroyFotoApi']);
    Route::post('/profil/kontak', [PortalController::class, 'updateKontakApi']);
    Route::post('/profil/password', [PortalController::class, 'updatePasswordApi']);
    Route::get('/anak-asuh', [EmployeeController::class, 'anakAsuhApi']);
    Route::get('/pembinaan', [CoachingCounselingController::class, 'apiIndex']);
    Route::get('/gmp/area', [OneGmpController::class, 'areaApi']);
    Route::get('/gmp/penilaian/{type}', [OneGmpController::class, 'penilaianFormApi']);
    Route::post('/gmp/penilaian', [OneGmpController::class, 'storePenilaianApi']);
    Route::get('/compliance', [OdrDailyComplianceController::class, 'apiDaily']);
    Route::post('/compliance/{assignment}/tasks', [OdrDailyComplianceController::class, 'apiToggleTask'])->whereNumber('assignment');
    Route::get('/qor/monthly', [QorApiController::class, 'monthly']);
    Route::get('/qor/dpr-daily', [QorApiController::class, 'dprDaily']);
    Route::get('/ojk', [OneReportingController::class, 'ojkApi']);
    Route::post('/ojk', [OneReportingController::class, 'ojkUploadApi']);
    Route::delete('/ojk/{id}', [OneReportingController::class, 'ojkDestroyApi'])->whereNumber('id');
    Route::get('/setting-board', [OneSettingController::class, 'settingBoardApi']);
    Route::get('/roster', [RosterPersonalController::class, 'api']);
    Route::get('/hm', [HmDataController::class, 'api']);
    Route::get('/absensi', [AttendanceController::class, 'api']);
    Route::get('/mor', [MorRaportController::class, 'show']);
    Route::get('/nomor-penting', [NomorPentingController::class, 'apiMobile']);
    Route::get('/izin', [IzinKaryawanController::class, 'apiIndex']);
    Route::post('/izin', [IzinKaryawanController::class, 'apiStore']);
    Route::post('/izin/{izin}/cancel', [IzinKaryawanController::class, 'apiCancel']);
    Route::delete('/izin/{izin}', [IzinKaryawanController::class, 'apiDestroy']);

    Route::prefix('assessment')->controller(AssessmentApiController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{assessment}/materi', 'materi');
        Route::post('/{assessment}/start', 'start');
        Route::get('/evaluation/{evaluation}', 'quiz');
        Route::post('/evaluation/{evaluation}/autosave', 'autosave');
        Route::post('/evaluation/{evaluation}/submit', 'submit');
        Route::get('/evaluation/{evaluation}/result', 'result');
    });
});
