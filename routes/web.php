<?php

use App\Http\Controllers\InternalMessageController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRatioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\CoachingClinicController;
use App\Http\Controllers\CoachingClinicDashboardController;
use App\Http\Controllers\CoachingClinicExportController;
use App\Http\Controllers\CoachingCounselingController;
use App\Http\Controllers\CoachingMateriController;
use App\Http\Controllers\CoachingProgressWizardController;
use App\Http\Controllers\CoachingTahapanController;
use App\Http\Controllers\DeviasiRekapPemulanganController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FatigueInterviewController;
use App\Http\Controllers\HmDataController;
use App\Http\Controllers\IzinKaryawanController;
use App\Http\Controllers\ManpowerPerformanceReportController;
use App\Http\Controllers\MasterSpdkController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\MyAssessmentController;
use App\Http\Controllers\MorDataController;
use App\Http\Controllers\MppDepartemenController;
use App\Http\Controllers\MppDashboardController;
use App\Http\Controllers\MppDetailController;
use App\Http\Controllers\MppMasterCategoryController;
use App\Http\Controllers\MppPhotoController;
use App\Http\Controllers\MppReportController;
use App\Http\Controllers\OneAcademyController;
use App\Http\Controllers\MonitoringGradeController;
use App\Http\Controllers\AcademyAssessmentController;
use App\Http\Controllers\AcademyMonitoringController;
use App\Http\Controllers\EvaluasiKompetensiController;
use App\Http\Controllers\BestEmployeeController;
use App\Http\Controllers\BestEmployeeParticipantController;
use App\Http\Controllers\BestEmployeeQuestionController;
use App\Http\Controllers\BestEmployeeResultController;
use App\Http\Controllers\OneCoreController;
use App\Http\Controllers\OneDmsController;
use App\Http\Controllers\OneGmpController;
use App\Http\Controllers\OnePowerController;
use App\Http\Controllers\OneProcedureController;
use App\Http\Controllers\OneReportingController;
use App\Http\Controllers\OneSettingController;
use App\Http\Controllers\OperatorUnfitController;
use App\Http\Controllers\PaktaIntegritasController;
use App\Http\Controllers\PemanggilanKaryawanController;
use App\Http\Controllers\PemulanganKaryawanController;
use App\Http\Controllers\OdrComplianceReportController;
use App\Http\Controllers\OdrDailyComplianceController;
use App\Http\Controllers\OdrGlSettingController;
use App\Http\Controllers\OdrIncidentController;
use App\Http\Controllers\OdrMasterComplianceController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProcedureAssessmentController;
use App\Http\Controllers\ProcedureCertificateController;
use App\Http\Controllers\ProcedureMonitoringController;
use App\Http\Controllers\QuartalOperatorReportController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\RosterDepartemenController;
use App\Http\Controllers\RosterPersonalController;
use App\Http\Controllers\SkPembinaanController;
use App\Http\Controllers\SuratPeringatanController;
use App\Http\Controllers\SuratTeguranController;
use App\Http\Controllers\ValidasiIstirahatController;
use App\Http\Controllers\SpDkController;
use App\Http\Controllers\JobMonitorController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\NomorPentingController;
use App\Http\Controllers\ForgotPasswordRequestController;
use App\Http\Controllers\GmpFindingController;
use Illuminate\Support\Facades\Route;

// Public Health Check Endpoint (for monitoring systems)
Route::get('/health', [HealthController::class, 'check'])->name('health.check');

// Verifikasi publik sertifikat Evaluasi Prosedur (target QR code, tanpa login)
Route::get('/sertifikat-prosedur/verify', [ProcedureCertificateController::class, 'verify'])->name('procedure-certificates.verify');

Route::get('/run-migration', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--seed' => true]);
    return 'Migration & Seeding completed: ' . \Illuminate\Support\Facades\Artisan::output();
});

Route::get('/', function () {
    return auth()->check() ? redirect('/portal') : redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/auth/sessions', [AuthController::class, 'activeSessions'])->name('auth.sessions');
    Route::delete('/auth/sessions/{sessionId}', [AuthController::class, 'revokeSession'])->name('auth.sessions.revoke');
    Route::post('/auth/unlock', [AuthController::class, 'unlockAccount'])->name('auth.unlock');

    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::get('/portal/data', [PortalController::class, 'mobileData'])->name('mobile.data');
    Route::get('/portal/aktivity', [PortalController::class, 'aktivity'])->name('mobile.aktivity');
    Route::get('/portal/profil', [PortalController::class, 'profil'])->name('mobile.profil');

    Route::get('/one-power', [OnePowerController::class, 'dashboard'])->name('one-power.dashboard');
    Route::get('/one-power/employees', [EmployeeController::class, 'index'])->name('one-power.employees.index');
    Route::get('/one-power/employees/inactive', [EmployeeController::class, 'inactive'])->name('one-power.employees.inactive');
    Route::get('/one-power/employees/create', [EmployeeController::class, 'create'])->name('one-power.employees.create');
    Route::post('/one-power/employees', [EmployeeController::class, 'store'])->name('one-power.employees.store');
    Route::post('/one-power/employees/import', [EmployeeController::class, 'import'])->name('one-power.employees.import');
    Route::get('/one-power/employees/template', [EmployeeController::class, 'downloadTemplate'])->name('one-power.employees.template');
    Route::get('/one-power/employees/export', [EmployeeController::class, 'export'])->name('one-power.employees.export');
    Route::post('/one-power/employees/bulk-delete-inactive', [EmployeeController::class, 'bulkDeleteInactive'])->name('one-power.employees.bulk-delete-inactive');
    Route::post('/one-power/employees/bulk-delete-all', [EmployeeController::class, 'bulkDeleteAll'])->name('one-power.employees.bulk-delete-all');
    Route::get('/one-power/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('one-power.employees.edit');
    Route::put('/one-power/employees/{employee}', [EmployeeController::class, 'update'])->name('one-power.employees.update');
    Route::delete('/one-power/employees/{employee}', [EmployeeController::class, 'destroy'])->name('one-power.employees.destroy');
    Route::post('/one-power/employees/{employee}/status', [EmployeeController::class, 'setStatus'])->name('one-power.employees.status');

    // Sahabat Pengusaha (GL - Mentee) & Print Profile
    Route::get('/one-power/employees/list-gl', [EmployeeController::class, 'listGL'])->name('one-power.employees.list-gl');
    Route::get('/one-power/employees/list_gl', fn () => redirect()->route('one-power.employees.list-gl'));
    Route::get('/one-power/employees/{gl}/anak-asuh', [EmployeeController::class, 'anakAsuh'])->name('one-power.employees.anak-asuh');
    Route::get('/one-power/employees/{gl}/select-anak-asuh', [EmployeeController::class, 'selectAnakAsuh'])->name('one-power.employees.select-anak-asuh');
    Route::post('/one-power/employees/bulk-store-asuh', [EmployeeController::class, 'bulkStoreAsuh'])->name('one-power.employees.bulk-store-asuh');
    Route::post('/one-power/employees/import-asuh', [EmployeeController::class, 'importAsuh'])->name('one-power.employees.import-asuh');
    Route::post('/one-power/employees/{employee}/set-asuh', [EmployeeController::class, 'setAsuh'])->name('one-power.employees.set-asuh');
    Route::delete('/one-power/employees/{employee}/remove-asuh', [EmployeeController::class, 'removeAsuh'])->name('one-power.employees.remove-asuh');
    Route::get('/one-power/employees/{employee}/print', [EmployeeController::class, 'print'])->name('one-power.employees.print');

    Route::get('/one-power/personal-data/hm', [HmDataController::class, 'index'])->name('one-power.hm-data.index');
    Route::get('/one-power/personal-data/hm/search-employee', [HmDataController::class, 'searchEmployee'])->name('one-power.hm-data.search-employee');
    Route::post('/one-power/personal-data/hm', [HmDataController::class, 'store'])->name('one-power.hm-data.store');
    Route::put('/one-power/personal-data/hm/{hm}', [HmDataController::class, 'update'])->name('one-power.hm-data.update');
    Route::delete('/one-power/personal-data/hm/{hm}', [HmDataController::class, 'destroy'])->name('one-power.hm-data.destroy');
    Route::post('/one-power/personal-data/hm/import', [HmDataController::class, 'import'])->name('one-power.hm-data.import');
    Route::post('/one-power/personal-data/hm/import-api', [HmDataController::class, 'importFromApi'])->name('one-power.hm-data.import-api');

    Route::get('/one-power/personal-data/attendance', [AttendanceController::class, 'index'])->name('one-power.attendance.index');
    Route::post('/one-power/personal-data/attendance/import-api', [AttendanceController::class, 'importFromApi'])->name('one-power.attendance.import-api');
    Route::put('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'update'])->name('one-power.attendance.update');
    Route::delete('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('one-power.attendance.destroy');

    Route::get('/one-power/personal-data/attendance-ratio', [AttendanceRatioController::class, 'index'])->name('one-power.attendance-ratio.index');
    Route::post('/one-power/personal-data/attendance-ratio/threshold', [AttendanceRatioController::class, 'saveThreshold'])->name('one-power.attendance-ratio.save-threshold');

    Route::get('/one-power/personal-data/mpr', [ManpowerPerformanceReportController::class, 'index'])->name('one-power.mpr.index');
    Route::get('/one-power/personal-data/mpr/search-employee', [ManpowerPerformanceReportController::class, 'searchEmployee'])->name('one-power.mpr.search-employee');
    // MPP Departemen Phase 1
    Route::get('/one-power/mpp-departemen', [MppDashboardController::class, 'index'])->name('one-power.mpp-departemen');

    // MPP Departemen - Analisis Staff vs Pengawas (landing/index page)
    Route::get('/one-power/mpp-departemen/analisis', [MppDepartemenController::class, 'index'])->name('one-power.mpp-departemen.analisis');
    Route::post('/one-power/mpp-departemen/analisis/target', [MppDepartemenController::class, 'updateTarget'])->name('one-power.mpp-departemen.update-target');

    // Master Categories
    Route::resource('one-power/mpp-master-categories', MppMasterCategoryController::class, ['as' => 'mpp'])->names([
        'index' => 'mpp-master-categories.index',
        'store' => 'mpp-master-categories.store',
        'update' => 'mpp-master-categories.update',
        'destroy' => 'mpp-master-categories.destroy',
    ]);
    Route::post('/one-power/mpp-master-categories/reorder', [MppMasterCategoryController::class, 'reorder'])->name('mpp-master-categories.reorder');

    // Photos
    Route::get('/one-power/mpp-departemen/photo-upload', [MppPhotoController::class, 'create'])->name('mpp-photo.create');
    Route::post('/one-power/mpp-departemen/photo-upload', [MppPhotoController::class, 'store'])->name('mpp-photo.store');
    Route::get('/one-power/mpp-departemen/photos/{jenis}', [MppPhotoController::class, 'showByJenis'])->name('mpp-photo.by-jenis');

    // MPP Phase 2 - Detail Pages
    Route::get('/one-power/mpp-departemen/daily-atr', [MppDetailController::class, 'dailyAtr'])->name('mpp-departemen.daily-atr');
    Route::get('/one-power/mpp-departemen/actual-mpp', [MppDetailController::class, 'actualMpp'])->name('mpp-departemen.actual-mpp');
    Route::get('/one-power/mpp-departemen/checkpoint-data', [MppDetailController::class, 'checkpointData'])->name('mpp-departemen.checkpoint-data');
    Route::get('/one-power/mpp-departemen/ojk-data', [MppDetailController::class, 'ojkData'])->name('mpp-departemen.ojk-data');
    Route::get('/one-power/mpp-departemen/kesiapan-operator', [MppDetailController::class, 'kesiapanOperator'])->name('mpp-departemen.kesiapan-operator');
    Route::get('/one-power/mpp-departemen/show-employees', [MppDetailController::class, 'showEmployees'])->name('mpp-departemen.show-employees');
    Route::get('/one-power/mpp-departemen/employee/{id}', [MppDetailController::class, 'showEmployeeDetail'])->name('mpp-departemen.employee-detail');

    // MPP Phase 2 - Export Routes
    Route::post('/one-power/mpp-departemen/export-daily-atr', [MppReportController::class, 'exportDailyAtr'])->name('mpp-report.export-daily-atr');
    Route::post('/one-power/mpp-departemen/export-actual-mpp', [MppReportController::class, 'exportActualMpp'])->name('mpp-report.export-actual-mpp');
    Route::post('/one-power/mpp-departemen/export-checkpoint', [MppReportController::class, 'exportCheckpoint'])->name('mpp-report.export-checkpoint');
    Route::post('/one-power/mpp-departemen/export-ojk', [MppReportController::class, 'exportOjk'])->name('mpp-report.export-ojk');
    Route::post('/one-power/mpp-departemen/export-kesiapan-trend', [MppReportController::class, 'exportKesiapanTrend'])->name('mpp-report.export-kesiapan-trend');
    Route::post('/one-power/mpp-departemen/export-employee-photos/{id}', [MppReportController::class, 'exportEmployeePhotos'])->name('mpp-report.export-employee-photos');

    Route::get('/one-power/quartal-operator-report', [QuartalOperatorReportController::class, 'index'])->name('one-power.quartal-operator-report.index');
    Route::get('/one-power/quartal-operator-report/dpr-daily', [QuartalOperatorReportController::class, 'getDprDaily'])->name('one-power.quartal-operator-report.dpr-daily');
    Route::get('/one-power/quartal-operator-report/template', [QuartalOperatorReportController::class, 'downloadTemplate'])->name('one-power.quartal-operator-report.template');
    Route::post('/one-power/quartal-operator-report/import', [QuartalOperatorReportController::class, 'import'])->name('one-power.quartal-operator-report.import');
    Route::post('/one-power/quartal-operator-report/recalculate', [QuartalOperatorReportController::class, 'recalculate'])->name('one-power.quartal-operator-report.recalculate');
    Route::post('/one-power/quartal-operator-report/reset', [QuartalOperatorReportController::class, 'reset'])->name('one-power.quartal-operator-report.reset');

    // Coaching Clinic & Materi Routes
    Route::resource('/one-power/coaching-clinic', CoachingClinicController::class)->names('coaching-clinic');
    Route::get('/one-power/coaching-clinic/search-employee', [CoachingClinicController::class, 'searchEmployee'])->name('coaching-clinic.search-employee');
    Route::get('/one-power/coaching-clinic-dashboard', [CoachingClinicDashboardController::class, 'index'])->name('coaching-clinic.dashboard');
    Route::resource('/one-power/coaching-tahapan', CoachingTahapanController::class)->names('coaching-tahapan');
    Route::post('/one-power/coaching-tahapan/{tahapan}/reorder', [CoachingTahapanController::class, 'reorder'])->name('coaching-tahapan.reorder');
    Route::get('/one-power/coaching-progress-wizard', [CoachingProgressWizardController::class, 'create'])->name('coaching-progress.create');
    Route::post('/one-power/coaching-progress-wizard', [CoachingProgressWizardController::class, 'store'])->name('coaching-progress.store');
    Route::post('/one-power/coaching-clinic/export', [CoachingClinicExportController::class, 'export'])->name('coaching-clinic.export');
    Route::resource('/one-power/coaching-materi', CoachingMateriController::class)->names('coaching-materi');

    Route::get('/one-power/roster-departemen', [RosterDepartemenController::class, 'index'])->name('one-power.roster-departemen.index');
    Route::post('/one-power/roster-departemen/update-cell', [RosterDepartemenController::class, 'updateCell'])->name('one-power.roster-departemen.update-cell');
    Route::post('/one-power/roster-departemen/save-google-sheet-urls', [RosterDepartemenController::class, 'saveGoogleSheetUrls'])->name('one-power.roster-departemen.save-urls');
    Route::post('/one-power/roster-departemen/import-google-sheet', [RosterDepartemenController::class, 'importGoogleSheet'])->name('one-power.roster-departemen.import-sheet');
    Route::post('/one-power/roster-departemen/import-file', [RosterDepartemenController::class, 'importFile'])->name('one-power.roster-departemen.import-file');
    Route::get('/one-power/roster-personal', [RosterPersonalController::class, 'index'])->name('one-power.roster-personal.index');
    Route::get('/one-power/roster-personal/search-employee', [RosterPersonalController::class, 'searchEmployee'])->name('one-power.roster-personal.search-employee');
    Route::post('/one-power/roster-personal/visible-months', [RosterPersonalController::class, 'saveVisibleMonths'])->name('one-power.roster-personal.save-visible-months');
    Route::get('/one-power/roster-personal/print', [RosterPersonalController::class, 'printRosterPdf'])->name('one-power.roster-personal.print');
    Route::get('/one-power/roster', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->canViewAllRosters()) {
            return redirect()->route('one-power.roster-departemen.index');
        }
        return redirect()->route('one-power.roster-personal.index');
    })->name('one-power.roster.index');
    Route::post('/one-power/roster/cell', [RosterController::class, 'updateCell'])->name('one-power.roster.update-cell');
    Route::post('/one-power/roster/import', [RosterController::class, 'import'])->name('one-power.roster.import');

    Route::get('/one-power/mor', [MorDataController::class, 'index'])->name('one-power.mor.index');
    Route::get('/one-power/mor/search-employee', [MorDataController::class, 'searchEmployee'])->name('one-power.mor.search-employee');
    Route::post('/one-power/mor', [MorDataController::class, 'store'])->name('one-power.mor.store');
    Route::put('/one-power/mor/{morDatum}', [MorDataController::class, 'update'])->name('one-power.mor.update');
    Route::delete('/one-power/mor/{morDatum}', [MorDataController::class, 'destroy'])->name('one-power.mor.destroy');

    Route::get('/one-power/deviasi-operator/unfit', [OperatorUnfitController::class, 'index'])->name('one-power.operator-unfit.index');
    Route::get('/one-power/deviasi-operator/unfit/search-employee', [OperatorUnfitController::class, 'searchEmployee'])->name('one-power.operator-unfit.search-employee');
    Route::get('/one-power/deviasi-operator/unfit/create', [OperatorUnfitController::class, 'create'])->name('one-power.operator-unfit.create');
    Route::post('/one-power/deviasi-operator/unfit', [OperatorUnfitController::class, 'store'])->name('one-power.operator-unfit.store');
    Route::get('/one-power/deviasi-operator/unfit/{unfit}/edit', [OperatorUnfitController::class, 'edit'])->name('one-power.operator-unfit.edit');
    Route::put('/one-power/deviasi-operator/unfit/{unfit}', [OperatorUnfitController::class, 'update'])->name('one-power.operator-unfit.update');
    Route::delete('/one-power/deviasi-operator/unfit/{unfit}', [OperatorUnfitController::class, 'destroy'])->name('one-power.operator-unfit.destroy');
    Route::post('/one-power/deviasi-operator/unfit/{unfit}/end', [OperatorUnfitController::class, 'endUnfit'])->name('one-power.operator-unfit.end');

    Route::get('/one-power/deviasi-operator/izin-karyawan', [IzinKaryawanController::class, 'index'])->name('one-power.izin-karyawan.index');
    Route::get('/one-power/deviasi-operator/izin-karyawan/export', [IzinKaryawanController::class, 'export'])->name('one-power.izin-karyawan.export');
    Route::get('/one-power/deviasi-operator/izin-karyawan/search-employee', [IzinKaryawanController::class, 'searchEmployee'])->name('one-power.izin-karyawan.search-employee');
    Route::get('/one-power/deviasi-operator/izin-karyawan/next-surat-number', [IzinKaryawanController::class, 'getNextSuratNumber'])->name('one-power.izin-karyawan.next-surat-number');
    Route::get('/one-power/deviasi-operator/izin-karyawan/create', [IzinKaryawanController::class, 'create'])->name('one-power.izin-karyawan.create');
    Route::post('/one-power/deviasi-operator/izin-karyawan', [IzinKaryawanController::class, 'store'])->name('one-power.izin-karyawan.store');
    Route::get('/one-power/deviasi-operator/izin-karyawan/{izin}/edit', [IzinKaryawanController::class, 'edit'])->name('one-power.izin-karyawan.edit');
    Route::put('/one-power/deviasi-operator/izin-karyawan/{izin}', [IzinKaryawanController::class, 'update'])->name('one-power.izin-karyawan.update');
    Route::delete('/one-power/deviasi-operator/izin-karyawan/{izin}', [IzinKaryawanController::class, 'destroy'])->name('one-power.izin-karyawan.destroy');
    Route::post('/one-power/deviasi-operator/izin-karyawan/{izin}/approve', [IzinKaryawanController::class, 'approve'])->name('one-power.izin-karyawan.approve');
    Route::post('/one-power/deviasi-operator/izin-karyawan/{izin}/decline', [IzinKaryawanController::class, 'decline'])->name('one-power.izin-karyawan.decline');
    Route::post('/one-power/deviasi-operator/izin-karyawan/{izin}/reset', [IzinKaryawanController::class, 'reset'])->name('one-power.izin-karyawan.reset');
    Route::post('/one-power/deviasi-operator/izin-karyawan/{izin}/cancel', [IzinKaryawanController::class, 'cancel'])->name('one-power.izin-karyawan.cancel');

    Route::get('/one-power/deviasi-operator/rekap-pemulangan', [DeviasiRekapPemulanganController::class, 'index'])->name('one-power.deviasi-operator.rekap-pemulangan.index');
    Route::get('/one-power/deviasi-operator/rekap-pemulangan/search-employee', [DeviasiRekapPemulanganController::class, 'searchEmployee'])->name('one-power.deviasi-operator.rekap-pemulangan.search-employee');
    Route::get('/one-power/deviasi-operator/rekap-pemulangan/template', [DeviasiRekapPemulanganController::class, 'downloadTemplate'])->name('one-power.deviasi-operator.rekap-pemulangan.template');
    Route::post('/one-power/deviasi-operator/rekap-pemulangan/import', [DeviasiRekapPemulanganController::class, 'import'])->name('one-power.deviasi-operator.rekap-pemulangan.import');
    Route::post('/one-power/deviasi-operator/rekap-pemulangan', [DeviasiRekapPemulanganController::class, 'store'])->name('one-power.deviasi-operator.rekap-pemulangan.store');
    Route::put('/one-power/deviasi-operator/rekap-pemulangan/{rekapPemulangan}', [DeviasiRekapPemulanganController::class, 'update'])->name('one-power.deviasi-operator.rekap-pemulangan.update');
    Route::delete('/one-power/deviasi-operator/rekap-pemulangan/{rekapPemulangan}', [DeviasiRekapPemulanganController::class, 'destroy'])->name('one-power.deviasi-operator.rekap-pemulangan.destroy');

    Route::get('/one-power/pembinaan/berita-acara', [BeritaAcaraController::class, 'index'])->name('one-power.berita-acara.index');
    Route::get('/one-power/pembinaan/berita-acara/search-employee', [BeritaAcaraController::class, 'searchEmployee'])->name('one-power.berita-acara.search-employee');
    Route::post('/one-power/pembinaan/berita-acara/import', [BeritaAcaraController::class, 'import'])->name('one-power.berita-acara.import');
    Route::post('/one-power/pembinaan/berita-acara/settings', [BeritaAcaraController::class, 'saveSettings'])->name('one-power.berita-acara.settings');
    Route::get('/one-power/pembinaan/berita-acara/template', [BeritaAcaraController::class, 'downloadTemplate'])->name('one-power.berita-acara.template');
    Route::get('/one-power/pembinaan/berita-acara/create', [BeritaAcaraController::class, 'create'])->name('one-power.berita-acara.create');
    Route::post('/one-power/pembinaan/berita-acara', [BeritaAcaraController::class, 'store'])->name('one-power.berita-acara.store');
    Route::get('/one-power/pembinaan/berita-acara/{beritaAcara}/edit', [BeritaAcaraController::class, 'edit'])->name('one-power.berita-acara.edit');
    Route::put('/one-power/pembinaan/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'update'])->name('one-power.berita-acara.update');
    Route::delete('/one-power/pembinaan/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'destroy'])->name('one-power.berita-acara.destroy');
    Route::get('/one-power/pembinaan/berita-acara/{beritaAcara}/print', [BeritaAcaraController::class, 'show'])->name('one-power.berita-acara.print');

    Route::get('/one-power/pembinaan/coaching-counseling', [CoachingCounselingController::class, 'index'])->name('one-power.coaching-counseling.index');
    Route::post('/one-power/pembinaan/coaching-counseling/import', [CoachingCounselingController::class, 'import'])->name('one-power.coaching-counseling.import');
    Route::post('/one-power/pembinaan/coaching-counseling/settings', [CoachingCounselingController::class, 'saveSettings'])->name('one-power.coaching-counseling.settings');
    Route::get('/one-power/pembinaan/coaching-counseling/template', [CoachingCounselingController::class, 'downloadTemplate'])->name('one-power.coaching-counseling.template');
    Route::get('/one-power/pembinaan/coaching-counseling/search-employee', [CoachingCounselingController::class, 'searchEmployee'])->name('one-power.coaching-counseling.search-employee');
    Route::get('/one-power/pembinaan/coaching-counseling/print-form', [CoachingCounselingController::class, 'printBlankForm'])->name('one-power.coaching-counseling.print-blank');
    Route::get('/one-power/pembinaan/coaching-counseling/create', [CoachingCounselingController::class, 'create'])->name('one-power.coaching-counseling.create');
    Route::post('/one-power/pembinaan/coaching-counseling', [CoachingCounselingController::class, 'store'])->name('one-power.coaching-counseling.store');
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/edit', [CoachingCounselingController::class, 'edit'])->name('one-power.coaching-counseling.edit');
    Route::put('/one-power/pembinaan/coaching-counseling/{coachingCounseling}', [CoachingCounselingController::class, 'update'])->name('one-power.coaching-counseling.update');
    Route::delete('/one-power/pembinaan/coaching-counseling/{coachingCounseling}', [CoachingCounselingController::class, 'destroy'])->name('one-power.coaching-counseling.destroy');
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/print-form', [CoachingCounselingController::class, 'printForm'])->name('one-power.coaching-counseling.print-form');
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/print-result', [CoachingCounselingController::class, 'printResult'])->name('one-power.coaching-counseling.print-result');
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/print', [CoachingCounselingController::class, 'show'])->name('one-power.coaching-counseling.print');

    Route::get('/one-power/pembinaan/interview-fatigue', [FatigueInterviewController::class, 'index'])->name('one-power.fatigue-interview.index');
    Route::post('/one-power/pembinaan/interview-fatigue/import', [FatigueInterviewController::class, 'import'])->name('one-power.fatigue-interview.import');
    Route::get('/one-power/pembinaan/interview-fatigue/template', [FatigueInterviewController::class, 'downloadTemplate'])->name('one-power.fatigue-interview.template');
    Route::post('/one-power/pembinaan/interview-fatigue/settings', [FatigueInterviewController::class, 'saveSettings'])->name('one-power.fatigue-interview.settings');
    Route::get('/one-power/pembinaan/interview-fatigue/search-employee', [FatigueInterviewController::class, 'searchEmployee'])->name('one-power.fatigue-interview.search-employee');
    Route::get('/one-power/pembinaan/interview-fatigue/create', [FatigueInterviewController::class, 'create'])->name('one-power.fatigue-interview.create');
    Route::post('/one-power/pembinaan/interview-fatigue', [FatigueInterviewController::class, 'store'])->name('one-power.fatigue-interview.store');
    Route::get('/one-power/pembinaan/interview-fatigue/{fatigueInterview}/edit', [FatigueInterviewController::class, 'edit'])->name('one-power.fatigue-interview.edit');
    Route::put('/one-power/pembinaan/interview-fatigue/{fatigueInterview}', [FatigueInterviewController::class, 'update'])->name('one-power.fatigue-interview.update');
    Route::delete('/one-power/pembinaan/interview-fatigue/{fatigueInterview}', [FatigueInterviewController::class, 'destroy'])->name('one-power.fatigue-interview.destroy');
    Route::get('/one-power/pembinaan/interview-fatigue/{fatigueInterview}/print', [FatigueInterviewController::class, 'show'])->name('one-power.fatigue-interview.print');

    Route::get('/one-power/pembinaan/surat-teguran', [SuratTeguranController::class, 'index'])->name('one-power.surat-teguran.index');
    Route::post('/one-power/pembinaan/surat-teguran/settings', [SuratTeguranController::class, 'saveSettings'])->name('one-power.surat-teguran.settings');
    Route::get('/one-power/pembinaan/surat-teguran/search-employee', [SuratTeguranController::class, 'searchEmployee'])->name('one-power.surat-teguran.search-employee');
    Route::post('/one-power/pembinaan/surat-teguran/import', [SuratTeguranController::class, 'import'])->name('one-power.surat-teguran.import');
    Route::get('/one-power/pembinaan/surat-teguran/template', [SuratTeguranController::class, 'downloadTemplate'])->name('one-power.surat-teguran.template');
    Route::get('/one-power/pembinaan/surat-teguran/create', [SuratTeguranController::class, 'create'])->name('one-power.surat-teguran.create');
    Route::post('/one-power/pembinaan/surat-teguran', [SuratTeguranController::class, 'store'])->name('one-power.surat-teguran.store');
    Route::get('/one-power/pembinaan/surat-teguran/{suratTeguran}/edit', [SuratTeguranController::class, 'edit'])->name('one-power.surat-teguran.edit');
    Route::put('/one-power/pembinaan/surat-teguran/{suratTeguran}', [SuratTeguranController::class, 'update'])->name('one-power.surat-teguran.update');
    Route::delete('/one-power/pembinaan/surat-teguran/{suratTeguran}', [SuratTeguranController::class, 'destroy'])->name('one-power.surat-teguran.destroy');
    Route::get('/one-power/pembinaan/surat-teguran/{suratTeguran}/print', [SuratTeguranController::class, 'show'])->name('one-power.surat-teguran.print');

    Route::get('/one-power/pembinaan/pemulangan-karyawan', [PemulanganKaryawanController::class, 'index'])->name('one-power.pemulangan-karyawan.index');
    Route::post('/one-power/pembinaan/pemulangan-karyawan/settings', [PemulanganKaryawanController::class, 'saveSettings'])->name('one-power.pemulangan-karyawan.settings');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/search-employee', [PemulanganKaryawanController::class, 'searchEmployee'])->name('one-power.pemulangan-karyawan.search-employee');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/next-number', [PemulanganKaryawanController::class, 'nextNumber'])->name('one-power.pemulangan-karyawan.next-number');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/create', [PemulanganKaryawanController::class, 'create'])->name('one-power.pemulangan-karyawan.create');
    Route::post('/one-power/pembinaan/pemulangan-karyawan', [PemulanganKaryawanController::class, 'store'])->name('one-power.pemulangan-karyawan.store');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}/edit', [PemulanganKaryawanController::class, 'edit'])->name('one-power.pemulangan-karyawan.edit');
    Route::put('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}', [PemulanganKaryawanController::class, 'update'])->name('one-power.pemulangan-karyawan.update');
    Route::delete('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}', [PemulanganKaryawanController::class, 'destroy'])->name('one-power.pemulangan-karyawan.destroy');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}/print', [PemulanganKaryawanController::class, 'show'])->name('one-power.pemulangan-karyawan.print');

    Route::get('/one-power/pembinaan/pakta-integritas', [PaktaIntegritasController::class, 'index'])->name('one-power.pakta-integritas.index');
    Route::post('/one-power/pembinaan/pakta-integritas/settings', [PaktaIntegritasController::class, 'saveSettings'])->name('one-power.pakta-integritas.settings');
    Route::get('/one-power/pembinaan/pakta-integritas/search-employee', [PaktaIntegritasController::class, 'searchEmployee'])->name('one-power.pakta-integritas.search-employee');
    Route::post('/one-power/pembinaan/pakta-integritas/import', [PaktaIntegritasController::class, 'import'])->name('one-power.pakta-integritas.import');
    Route::get('/one-power/pembinaan/pakta-integritas/template', [PaktaIntegritasController::class, 'downloadTemplate'])->name('one-power.pakta-integritas.template');
    Route::get('/one-power/pembinaan/pakta-integritas/next-number', [PaktaIntegritasController::class, 'nextNumber'])->name('one-power.pakta-integritas.next-number');
    Route::get('/one-power/pembinaan/pakta-integritas/create', [PaktaIntegritasController::class, 'create'])->name('one-power.pakta-integritas.create');
    Route::post('/one-power/pembinaan/pakta-integritas', [PaktaIntegritasController::class, 'store'])->name('one-power.pakta-integritas.store');
    Route::get('/one-power/pembinaan/pakta-integritas/{paktaIntegrita}/edit', [PaktaIntegritasController::class, 'edit'])->name('one-power.pakta-integritas.edit');
    Route::put('/one-power/pembinaan/pakta-integritas/{paktaIntegrita}', [PaktaIntegritasController::class, 'update'])->name('one-power.pakta-integritas.update');
    Route::delete('/one-power/pembinaan/pakta-integritas/{paktaIntegrita}', [PaktaIntegritasController::class, 'destroy'])->name('one-power.pakta-integritas.destroy');
    Route::get('/one-power/pembinaan/pakta-integritas/{paktaIntegrita}/print', [PaktaIntegritasController::class, 'show'])->name('one-power.pakta-integritas.print');
    Route::get('/one-power/pembinaan/pakta-integritas/{paktaIntegrita}/print-blank', [PaktaIntegritasController::class, 'printBlank'])->name('one-power.pakta-integritas.print-blank');

    Route::get('/one-power/pembinaan/validasi-istirahat', [ValidasiIstirahatController::class, 'index'])->name('one-power.validasi-istirahat.index');
    Route::post('/one-power/pembinaan/validasi-istirahat/settings', [ValidasiIstirahatController::class, 'saveSettings'])->name('one-power.validasi-istirahat.settings');
    Route::get('/one-power/pembinaan/validasi-istirahat/search-employee', [ValidasiIstirahatController::class, 'searchEmployee'])->name('one-power.validasi-istirahat.search-employee');
    Route::get('/one-power/pembinaan/validasi-istirahat/next-number', [ValidasiIstirahatController::class, 'nextNumber'])->name('one-power.validasi-istirahat.next-number');
    Route::get('/one-power/pembinaan/validasi-istirahat/create', [ValidasiIstirahatController::class, 'create'])->name('one-power.validasi-istirahat.create');
    Route::post('/one-power/pembinaan/validasi-istirahat', [ValidasiIstirahatController::class, 'store'])->name('one-power.validasi-istirahat.store');
    Route::get('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}/edit', [ValidasiIstirahatController::class, 'edit'])->name('one-power.validasi-istirahat.edit');
    Route::put('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}', [ValidasiIstirahatController::class, 'update'])->name('one-power.validasi-istirahat.update');
    Route::delete('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}', [ValidasiIstirahatController::class, 'destroy'])->name('one-power.validasi-istirahat.destroy');
    Route::get('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}/print', [ValidasiIstirahatController::class, 'show'])->name('one-power.validasi-istirahat.print');

    Route::get('/one-power/pembinaan/pemanggilan-karyawan', [PemanggilanKaryawanController::class, 'index'])->name('one-power.pemanggilan-karyawan.index');
    Route::post('/one-power/pembinaan/pemanggilan-karyawan/settings', [PemanggilanKaryawanController::class, 'saveSettings'])->name('one-power.pemanggilan-karyawan.settings');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/search-employee', [PemanggilanKaryawanController::class, 'searchEmployee'])->name('one-power.pemanggilan-karyawan.search-employee');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/next-number', [PemanggilanKaryawanController::class, 'nextNumber'])->name('one-power.pemanggilan-karyawan.next-number');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/create', [PemanggilanKaryawanController::class, 'create'])->name('one-power.pemanggilan-karyawan.create');
    Route::post('/one-power/pembinaan/pemanggilan-karyawan', [PemanggilanKaryawanController::class, 'store'])->name('one-power.pemanggilan-karyawan.store');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}/edit', [PemanggilanKaryawanController::class, 'edit'])->name('one-power.pemanggilan-karyawan.edit');
    Route::put('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}', [PemanggilanKaryawanController::class, 'update'])->name('one-power.pemanggilan-karyawan.update');
    Route::delete('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}', [PemanggilanKaryawanController::class, 'destroy'])->name('one-power.pemanggilan-karyawan.destroy');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}/print', [PemanggilanKaryawanController::class, 'show'])->name('one-power.pemanggilan-karyawan.print');

    Route::get('/one-power/pembinaan/surat-peringatan', [SuratPeringatanController::class, 'index'])->name('one-power.surat-peringatan.index');
    Route::post('/one-power/pembinaan/surat-peringatan/settings', [SuratPeringatanController::class, 'saveSettings'])->name('one-power.surat-peringatan.settings');
    Route::get('/one-power/pembinaan/surat-peringatan/search-employee', [SuratPeringatanController::class, 'searchEmployee'])->name('one-power.surat-peringatan.search-employee');
    Route::get('/one-power/pembinaan/surat-peringatan/check-active', [SuratPeringatanController::class, 'checkActive'])->name('one-power.surat-peringatan.check-active');
    Route::get('/one-power/pembinaan/surat-peringatan/next-number', [SuratPeringatanController::class, 'nextNumber'])->name('one-power.surat-peringatan.next-number');
    Route::post('/one-power/pembinaan/surat-peringatan/import', [SuratPeringatanController::class, 'import'])->name('one-power.surat-peringatan.import');
    Route::get('/one-power/pembinaan/surat-peringatan/template', [SuratPeringatanController::class, 'downloadTemplate'])->name('one-power.surat-peringatan.template');
    Route::get('/one-power/pembinaan/surat-peringatan/create', [SuratPeringatanController::class, 'create'])->name('one-power.surat-peringatan.create');
    Route::post('/one-power/pembinaan/surat-peringatan', [SuratPeringatanController::class, 'store'])->name('one-power.surat-peringatan.store');
    Route::get('/one-power/pembinaan/surat-peringatan/{suratPeringatan}/edit', [SuratPeringatanController::class, 'edit'])->name('one-power.surat-peringatan.edit');
    Route::put('/one-power/pembinaan/surat-peringatan/{suratPeringatan}', [SuratPeringatanController::class, 'update'])->name('one-power.surat-peringatan.update');
    Route::delete('/one-power/pembinaan/surat-peringatan/{suratPeringatan}', [SuratPeringatanController::class, 'destroy'])->name('one-power.surat-peringatan.destroy');
    Route::get('/one-power/pembinaan/surat-peringatan/{suratPeringatan}/print', [SuratPeringatanController::class, 'show'])->name('one-power.surat-peringatan.print');

    Route::get('/one-power/pembinaan/sk-pembinaan', [SkPembinaanController::class, 'index'])->name('one-power.sk-pembinaan.index');
    Route::get('/one-power/pembinaan/sk-pembinaan/search-employee', [SkPembinaanController::class, 'searchEmployee'])->name('one-power.sk-pembinaan.search-employee');
    Route::get('/one-power/pembinaan/sk-pembinaan/search-spdk', [SkPembinaanController::class, 'searchSpdk'])->name('one-power.sk-pembinaan.search-spdk');
    Route::get('/one-power/pembinaan/sk-pembinaan/next-number', [SkPembinaanController::class, 'nextNumber'])->name('one-power.sk-pembinaan.next-number');
    Route::post('/one-power/pembinaan/sk-pembinaan/import', [SkPembinaanController::class, 'import'])->name('one-power.sk-pembinaan.import');
    Route::get('/one-power/pembinaan/sk-pembinaan/template', [SkPembinaanController::class, 'downloadTemplate'])->name('one-power.sk-pembinaan.template');
    Route::get('/one-power/pembinaan/sk-pembinaan/create', [SkPembinaanController::class, 'create'])->name('one-power.sk-pembinaan.create');
    Route::post('/one-power/pembinaan/sk-pembinaan', [SkPembinaanController::class, 'store'])->name('one-power.sk-pembinaan.store');
    Route::get('/one-power/pembinaan/sk-pembinaan/{skPembinaan}/edit', [SkPembinaanController::class, 'edit'])->name('one-power.sk-pembinaan.edit');
    Route::put('/one-power/pembinaan/sk-pembinaan/{skPembinaan}', [SkPembinaanController::class, 'update'])->name('one-power.sk-pembinaan.update');
    Route::delete('/one-power/pembinaan/sk-pembinaan/{skPembinaan}', [SkPembinaanController::class, 'destroy'])->name('one-power.sk-pembinaan.destroy');
    Route::get('/one-power/pembinaan/sk-pembinaan/{skPembinaan}/print', [SkPembinaanController::class, 'show'])->name('one-power.sk-pembinaan.print');

    Route::get('/one-academy', [OneAcademyController::class, 'dashboard'])->name('one-academy.dashboard');
    Route::post('/one-academy/store-pengajuan', [OneAcademyController::class, 'storePengajuan'])->name('one-academy.store-pengajuan');
    Route::delete('/one-academy/delete-pengajuan/{id}', [OneAcademyController::class, 'deletePengajuan'])->name('one-academy.delete-pengajuan');
    Route::get('/one-academy/odp-database', [OneAcademyController::class, 'odpList'])->name('one-academy.odp-database');
    Route::post('/one-academy/store-odp-karyawan', [OneAcademyController::class, 'storeOdpKaryawan'])->name('one-academy.store-odp-karyawan');
    Route::get('/one-academy/monitoring-grade', [MonitoringGradeController::class, 'index'])->name('one-academy.monitoring-grade.index');
    Route::post('/one-academy/monitoring-grade/store-grade', [MonitoringGradeController::class, 'storeGrade'])->name('one-academy.monitoring-grade.store-grade');
    Route::post('/one-academy/monitoring-grade/store-versatility', [MonitoringGradeController::class, 'storeVersatility'])->name('one-academy.monitoring-grade.store-versatility');
    Route::post('/one-academy/monitoring-grade/sync-grade', [MonitoringGradeController::class, 'syncGrade'])->name('one-academy.monitoring-grade.sync-grade');
    Route::post('/one-academy/monitoring-grade/sync-versatility', [MonitoringGradeController::class, 'syncVersatility'])->name('one-academy.monitoring-grade.sync-versatility');

    Route::get('/one-academy/evaluasi-kompetensi', [EvaluasiKompetensiController::class, 'index'])->name('one-academy.evaluasi-kompetensi.index');
    Route::get('/one-academy/evaluasi-kompetensi/materi/view/{materi}', [EvaluasiKompetensiController::class, 'viewMateri'])->name('one-academy.evaluasi-kompetensi.materi.view');
    Route::post('/one-academy/evaluasi-kompetensi/materi', [EvaluasiKompetensiController::class, 'storeMateri'])->name('one-academy.evaluasi-kompetensi.materi.store');
    Route::post('/one-academy/evaluasi-kompetensi/materi/{materi}', [EvaluasiKompetensiController::class, 'updateMateri'])->name('one-academy.evaluasi-kompetensi.materi.update');
    Route::delete('/one-academy/evaluasi-kompetensi/materi/{materi}', [EvaluasiKompetensiController::class, 'deleteMateri'])->name('one-academy.evaluasi-kompetensi.materi.delete');

    Route::prefix('one-academy/evaluasi-kompetensi/assessment')->name('one-academy.evaluasi-kompetensi.assessment.')->controller(AcademyAssessmentController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{assessment}', 'update')->name('update');
        Route::delete('/{assessment}', 'destroy')->name('destroy');
        Route::post('/{assessment}/publish', 'publish')->name('publish');
        Route::post('/{assessment}/retrigger', 'retrigger')->name('retrigger');
        Route::post('/{assessment}/archive', 'archive')->name('archive');
        Route::put('/{assessment}/targets', 'saveTargets')->name('targets');
        Route::post('/{assessment}/questions', 'storeQuestion')->name('questions.store');
        Route::put('/questions/{question}', 'updateQuestion')->name('questions.update');
        Route::delete('/questions/{question}', 'destroyQuestion')->name('questions.destroy');
    });

    Route::post('/one-academy/evaluasi-kompetensi/record-result', [EvaluasiKompetensiController::class, 'recordResult'])->name('one-academy.evaluasi-kompetensi.record-result');
    Route::get('/one-academy/evaluasi-kompetensi/raport/{nrp}', [EvaluasiKompetensiController::class, 'raportDetail'])->name('one-academy.evaluasi-kompetensi.raport.detail');
    Route::prefix('one-academy/evaluasi-kompetensi/monitoring')->name('one-academy.evaluasi-kompetensi.monitoring.')->controller(AcademyMonitoringController::class)->group(function () {
        Route::get('/export/excel', 'exportExcel')->name('export-excel');
        Route::get('/export/pdf', 'exportPdf')->name('export-pdf');
        Route::get('/{result}', 'show')->name('show');
        Route::delete('/{assessment}/{nrp}', 'destroy')->name('destroy');
    });

    Route::get('/one-academy/best-employee', [BestEmployeeController::class, 'index'])->name('one-academy.best-employee.index');
    Route::post('/one-academy/best-employee', [BestEmployeeController::class, 'store'])->name('one-academy.best-employee.store');
    Route::put('/one-academy/best-employee/{campaign}', [BestEmployeeController::class, 'update'])->name('one-academy.best-employee.update');
    Route::post('/one-academy/best-employee/{campaign}/publish', [BestEmployeeController::class, 'publish'])->name('one-academy.best-employee.publish');
    Route::post('/one-academy/best-employee/{campaign}/cancel', [BestEmployeeController::class, 'cancel'])->name('one-academy.best-employee.cancel');
    Route::delete('/one-academy/best-employee/{campaign}', [BestEmployeeController::class, 'destroy'])->name('one-academy.best-employee.destroy');

    Route::post('/one-academy/best-employee/{campaign}/questions', [BestEmployeeQuestionController::class, 'store'])->name('one-academy.best-employee.questions.store');
    Route::post('/one-academy/best-employee/{campaign}/questions/reorder', [BestEmployeeQuestionController::class, 'reorder'])->name('one-academy.best-employee.questions.reorder');
    Route::delete('/one-academy/best-employee/{campaign}/questions/{question}', [BestEmployeeQuestionController::class, 'destroy'])->name('one-academy.best-employee.questions.destroy');

    Route::get('/one-academy/best-employee/employee-filter-options', [BestEmployeeParticipantController::class, 'filterOptions'])->name('one-academy.best-employee.participants.filter-options');
    Route::get('/one-academy/best-employee/{campaign}/participants/candidates', [BestEmployeeParticipantController::class, 'candidates'])->name('one-academy.best-employee.participants.candidates');
    Route::post('/one-academy/best-employee/{campaign}/participants/assign', [BestEmployeeParticipantController::class, 'assign'])->name('one-academy.best-employee.participants.assign');
    Route::post('/one-academy/best-employee/{campaign}/participants/remove', [BestEmployeeParticipantController::class, 'remove'])->name('one-academy.best-employee.participants.remove');

    Route::get('/one-academy/best-employee/{campaign}/export', [BestEmployeeResultController::class, 'export'])->name('one-academy.best-employee.export');
    Route::get('/one-academy/best-employee/attempts/{attempt}', [BestEmployeeResultController::class, 'show'])->name('one-academy.best-employee.attempts.show');
    Route::post('/one-academy/best-employee/attempts/{attempt}/reset', [BestEmployeeResultController::class, 'reset'])->name('one-academy.best-employee.attempts.reset');
    Route::post('/one-academy/best-employee/answers/{answer}/grade', [BestEmployeeResultController::class, 'grade'])->name('one-academy.best-employee.answers.grade');

    Route::get('/one-core', [OneCoreController::class, 'dashboard'])->name('one-core.dashboard');
    Route::post('/one-core/users', [OneCoreController::class, 'storeUser'])->name('one-core.users.store');
    Route::post('/one-core/unlock', [OneCoreController::class, 'unlock'])->name('one-core.unlock');
    Route::post('/one-core/lock', [OneCoreController::class, 'lock'])->name('one-core.lock');
    Route::put('/one-core/users/{user}/role', [OneCoreController::class, 'updateUserRole'])->name('one-core.users.role');
    Route::post('/one-core/users/{user}/reset-password', [OneCoreController::class, 'resetPassword'])->name('one-core.users.reset-password');

    Route::get('/one-core/roles', [OneCoreController::class, 'roles'])->name('one-core.roles.index');
    Route::post('/one-core/roles', [OneCoreController::class, 'storeRole'])->name('one-core.roles.store');
    Route::put('/one-core/roles/{role}', [OneCoreController::class, 'updateRole'])->name('one-core.roles.update');
    Route::delete('/one-core/roles/{role}', [OneCoreController::class, 'destroyRole'])->name('one-core.roles.destroy');
    Route::get('/one-core/roles/{role}', [OneCoreController::class, 'roleDetail'])->name('one-core.roles.detail');
    Route::put('/one-core/roles/{role}/menu/{module}', [OneCoreController::class, 'updateRoleMenuAccess'])->name('one-core.roles.menu.update');

    // Unit Produksi
    Route::get('/one-core/units', [UnitController::class, 'index'])->name('one-core.units.index');
    Route::post('/one-core/units', [UnitController::class, 'store'])->name('one-core.units.store');
    Route::put('/one-core/units/{unit}', [UnitController::class, 'update'])->name('one-core.units.update');
    Route::delete('/one-core/units/{unit}', [UnitController::class, 'destroy'])->name('one-core.units.destroy');
    Route::post('/one-core/units/settings', [UnitController::class, 'updateSettings'])->name('one-core.units.settings');

    // Master SPDK
    Route::get('/one-core/spdk', [MasterSpdkController::class, 'index'])->name('one-core.spdk.index');
    Route::post('/one-core/spdk', [MasterSpdkController::class, 'store'])->name('one-core.spdk.store');
    Route::put('/one-core/spdk/{spdk}', [MasterSpdkController::class, 'update'])->name('one-core.spdk.update');
    Route::delete('/one-core/spdk/{spdk}', [MasterSpdkController::class, 'destroy'])->name('one-core.spdk.destroy');

    // Nomor Penting
    Route::get('/one-core/nomor-penting/api/list', [NomorPentingController::class, 'apiList'])->name('one-core.nomor-penting.api-list');
    Route::get('/one-core/nomor-penting', [NomorPentingController::class, 'index'])->name('one-core.nomor-penting.index');
    Route::post('/one-core/nomor-penting', [NomorPentingController::class, 'store'])->name('one-core.nomor-penting.store');
    Route::put('/one-core/nomor-penting/{nomorPenting}', [NomorPentingController::class, 'update'])->name('one-core.nomor-penting.update');
    Route::delete('/one-core/nomor-penting/{nomorPenting}', [NomorPentingController::class, 'destroy'])->name('one-core.nomor-penting.destroy');

    // Reset Password Requests
    Route::get('/one-core/reset-password-requests', [ForgotPasswordRequestController::class, 'index'])->name('one-core.reset-password-requests.index');
    Route::put('/one-core/reset-password-requests/{forgotPasswordRequest}/complete', [ForgotPasswordRequestController::class, 'complete'])->name('one-core.reset-password-requests.complete');

    Route::get('/one-dms', function () {
        return redirect()->route('one-dms.dashboard');
    });
    Route::get('/one-dms/dashboard', [OneDmsController::class, 'dashboard'])->name('one-dms.dashboard');
    Route::get('/one-dms/formulir', [OneDmsController::class, 'formulir'])->name('one-dms.formulir');
    Route::get('/one-dms/formulir/print', [OneDmsController::class, 'printFormulirPdf'])->name('one-dms.formulir.print');
    Route::post('/one-dms/store', [OneDmsController::class, 'storeDms'])->name('one-dms.store');
    Route::delete('/one-dms/delete/{id}', [OneDmsController::class, 'deleteDms'])->name('one-dms.delete');
    Route::post('/one-dms/save-petugas', [OneDmsController::class, 'savePetugas'])->name('one-dms.save-petugas');
    Route::post('/one-dms/formulir/settings', [OneDmsController::class, 'saveFormSettings'])->name('one-dms.formulir.settings');
    Route::get('/one-dms/data-icc/{category}', [OneDmsController::class, 'dataIcc'])->name('one-dms.data-icc');
    Route::post('/one-dms/data-icc/import', [OneDmsController::class, 'importIcc'])->name('one-dms.data-icc.import');
    Route::post('/one-dms/data-icc/update', [OneDmsController::class, 'saveIcc'])->name('one-dms.data-icc.update');
    Route::post('/one-dms/izin-fatigue/store', [OneDmsController::class, 'saveIzinFatigue'])->name('one-dms.izin-fatigue.store');
    Route::delete('/one-dms/data-icc/delete/{id}', [OneDmsController::class, 'deleteIcc'])->name('one-dms.data-icc.delete');

    Route::get('/one-gmp', function () {
        return redirect()->route('one-gmp.area');
    });
    Route::get('/one-gmp/dashboard', [OneGmpController::class, 'dashboard'])->name('one-gmp.dashboard');
    Route::get('/one-gmp/monitoring', [OneGmpController::class, 'monitoring'])->name('one-gmp.monitoring');
    Route::get('/one-gmp/area/{type?}', [OneGmpController::class, 'area'])->name('one-gmp.area');
    Route::post('/one-gmp/questions/{no}/weight', [OneGmpController::class, 'updateQuestionWeight'])->name('one-gmp.question.weight');
    Route::delete('/one-gmp/penilaian/{id}', [OneGmpController::class, 'destroyPenilaian'])->name('one-gmp.penilaian.destroy');
    Route::get('/one-gmp/trend', [OneGmpController::class, 'trend'])->name('one-gmp.trend');
    Route::get('/one-gmp/penilaian/{type}', [OneGmpController::class, 'penilaian'])->name('one-gmp.penilaian');
    Route::post('/one-gmp/penilaian/store', [OneGmpController::class, 'storePenilaian'])->name('one-gmp.penilaian.store');
    Route::prefix('one-gmp/temuan')->controller(GmpFindingController::class)->name('one-gmp.temuan')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->name('.store');
        Route::get('/{finding}', 'show')->name('.show');
        Route::post('/{finding}/pic', 'assignPic')->name('.pic');
        Route::post('/{finding}/progress', 'updateProgress')->name('.progress');
        Route::post('/{finding}/verifikasi', 'verify')->name('.verify');
    });
    Route::get('/one-gmp/approval', [OneGmpController::class, 'approval'])->name('one-gmp.approval');
    Route::post('/one-gmp/approval/approve/{id}', [OneGmpController::class, 'approveForm'])->name('one-gmp.approval.approve');
    Route::post('/one-gmp/approval/reject/{id}', [OneGmpController::class, 'rejectForm'])->name('one-gmp.approval.reject');
    Route::post('/one-gmp/approval/return/{id}', [OneGmpController::class, 'returnForm'])->name('one-gmp.approval.return');
    Route::get('/one-gmp/master', [OneGmpController::class, 'masterGmp'])->name('one-gmp.master');
    Route::post('/one-gmp/master/store', [OneGmpController::class, 'storeMaster'])->name('one-gmp.master.store');
    Route::delete('/one-gmp/master/delete/{id}', [OneGmpController::class, 'deleteMaster'])->name('one-gmp.master.delete');

    Route::get('/one-reporting', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard');
    Route::get('/one-reporting/dashboard', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard-index');
    Route::prefix('one-reporting/daily')->controller(OdrDailyComplianceController::class)->group(function () {
        Route::get('/', 'daily')->name('one-reporting.daily');
        Route::post('/{assignment}/tasks', 'toggleTask')->name('one-reporting.daily.task-toggle');
        Route::get('/assignment', 'assignments')->name('one-reporting.assignments');
        Route::post('/assignment', 'store')->name('one-reporting.assignments.store');
        Route::put('/assignment/{assignment}', 'update')->name('one-reporting.assignments.update');
        Route::delete('/assignment/{assignment}', 'destroy')->name('one-reporting.assignments.destroy');
        Route::get('/assignment/template', 'template')->name('one-reporting.assignments.template');
        Route::post('/assignment/import', 'import')->name('one-reporting.assignments.import');
    });
    
    Route::get('/one-reporting/laporan', [OdrComplianceReportController::class, 'index'])->name('one-reporting.laporan');
    Route::get('/one-reporting/laporan/export', [OdrComplianceReportController::class, 'export'])->name('one-reporting.laporan.export');
    Route::prefix('one-reporting/perbaikan-pasca-insiden')->controller(OdrIncidentController::class)->name('one-reporting.incidents')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->name('.store');
        Route::put('/{incident}/status', 'updateStatus')->name('.status');
        Route::delete('/{incident}', 'destroy')->name('.destroy');
        Route::post('/{incident}/tindakan', 'storeAction')->name('.actions.store');
        Route::delete('/tindakan/{action}', 'destroyAction')->name('.actions.destroy');
        Route::post('/tindakan/{action}/evidence', 'storeEvidence')->name('.evidence.store');
        Route::delete('/evidence/{evidence}', 'destroyEvidence')->name('.evidence.destroy');
    });
    Route::prefix('one-reporting/setting-gl')->controller(OdrGlSettingController::class)->name('one-reporting.setting-gl')->group(function () {
        Route::get('/', 'index');
        Route::post('/parse', 'parse')->name('.parse');
        Route::delete('/batch', 'destroyBatch')->name('.destroy-batch');
        Route::delete('/{setting}', 'destroy')->name('.destroy');
    });
    
    Route::prefix('one-reporting/master')->controller(OdrMasterComplianceController::class)->name('one-reporting.master')->group(function () {
        Route::get('/', 'index');
        Route::post('/area', 'storeArea')->name('.area.store');
        Route::put('/area/{area}', 'updateArea')->name('.area.update');
        Route::delete('/area/{area}', 'destroyArea')->name('.area.destroy');
        Route::post('/unit', 'storeUnit')->name('.unit.store');
        Route::put('/unit/{unit}', 'updateUnit')->name('.unit.update');
        Route::delete('/unit/{unit}', 'destroyUnit')->name('.unit.destroy');
        Route::post('/aktivitas', 'storeAktivitas')->name('.aktivitas.store');
        Route::put('/aktivitas/{aktivitas}', 'updateAktivitas')->name('.aktivitas.update');
        Route::delete('/aktivitas/{aktivitas}', 'destroyAktivitas')->name('.aktivitas.destroy');
    });
    
    Route::get('/one-reporting/ojk', [OneReportingController::class, 'ojk'])->name('one-reporting.ojk');
    Route::post('/one-reporting/ojk/upload', [OneReportingController::class, 'uploadOjk'])->name('one-reporting.ojk.upload');
    Route::delete('/one-reporting/ojk/delete/{id}', [OneReportingController::class, 'deleteOjk'])->name('one-reporting.ojk.delete');

    Route::get('/one-setting', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard');
    Route::get('/one-setting/dashboard', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard-index');
    Route::get('/one-setting/setting-board', [OneSettingController::class, 'settingBoard'])->name('one-setting.setting-board');
    Route::post('/one-setting/boards/upload', [OneSettingController::class, 'uploadSettingBoard'])->name('one-setting.boards.upload');
    Route::post('/one-setting/boards/snapshot', [OneSettingController::class, 'saveSnapshotBoard'])->name('one-setting.boards.snapshot');
    Route::delete('/one-setting/boards/delete/{id}', [OneSettingController::class, 'deleteSettingBoard'])->name('one-setting.boards.delete');

    // Manpower & Roster Actions (dipakai oleh Monitoring Setting Manpower)
    Route::post('/one-setting/roster/update/{id}', [OneSettingController::class, 'updateRoster'])->name('one-setting.roster.update');
    Route::delete('/one-setting/roster/delete/{id}', [OneSettingController::class, 'deleteRoster'])->name('one-setting.roster.delete');
    Route::post('/one-setting/roster/clear', [OneSettingController::class, 'clearRoster'])->name('one-setting.roster.clear');
    Route::post('/one-setting/manpower/sync-gsheet', [OneSettingController::class, 'syncGsheetManpower'])->name('one-setting.manpower.sync-gsheet');

    // Unit Location Locks
    Route::post('/one-setting/location-locks', [OneSettingController::class, 'storeLocationLock'])->name('one-setting.location-locks.store');
    Route::delete('/one-setting/location-locks/{id}', [OneSettingController::class, 'deleteLocationLock'])->name('one-setting.location-locks.delete');
    Route::post('/one-setting/category-locks', [OneSettingController::class, 'storeCategoryLock'])->name('one-setting.category-locks.store');
    Route::delete('/one-setting/category-locks/{id}', [OneSettingController::class, 'deleteCategoryLock'])->name('one-setting.category-locks.delete');

    // Fleet Actions (dipakai oleh Monitoring Setting Fleet)
    Route::post('/one-setting/fleet/import', [OneSettingController::class, 'importFleet'])->name('one-setting.fleet.import');
    Route::post('/one-setting/fleet/delete', [OneSettingController::class, 'deleteFleetData'])->name('one-setting.fleet.delete');
    
    // Monitoring Views
    Route::get('/one-setting/monitoring-manpower', [OneSettingController::class, 'monitoringManpower'])->name('one-setting.monitoring-manpower');
    Route::get('/one-setting/monitoring-fleet', [OneSettingController::class, 'monitoringFleet'])->name('one-setting.monitoring-fleet');

    // Settings
    Route::get('/one-setting/gl', [OneSettingController::class, 'legacyGl'])->name('one-setting.gl');
    Route::post('/one-setting/gl', [OneSettingController::class, 'storeLegacyGl'])->name('one-setting.gl.store');
    Route::delete('/one-setting/gl/{id}', [OneSettingController::class, 'deleteLegacyGl'])->name('one-setting.gl.delete');

    Route::get('/one-setting/unit-support', [OneSettingController::class, 'legacyUnitSupport'])->name('one-setting.unit-support');
    Route::post('/one-setting/unit-support', [OneSettingController::class, 'storeLegacyUnitSupport'])->name('one-setting.unit-support.store');
    Route::delete('/one-setting/unit-support/{id}', [OneSettingController::class, 'deleteLegacyUnitSupport'])->name('one-setting.unit-support.delete');

    Route::get('/one-setting/dozer-pc', [OneSettingController::class, 'legacyDozerPc'])->name('one-setting.dozer-pc');
    Route::post('/one-setting/dozer-pc', [OneSettingController::class, 'storeLegacyDozerPc'])->name('one-setting.dozer-pc.store');
    Route::delete('/one-setting/dozer-pc/{id}', [OneSettingController::class, 'deleteLegacyDozerPc'])->name('one-setting.dozer-pc.delete');

    Route::get('/one-setting/pw-cp', [OneSettingController::class, 'legacyPwCp'])->name('one-setting.pw-cp');
    Route::post('/one-setting/pw-cp', [OneSettingController::class, 'storeLegacyPwCp'])->name('one-setting.pw-cp.store');
    Route::delete('/one-setting/pw-cp/{id}', [OneSettingController::class, 'deleteLegacyPwCp'])->name('one-setting.pw-cp.delete');
    Route::post('/one-setting/pw-cp/clear', [OneSettingController::class, 'clearLegacyPwCp'])->name('one-setting.pw-cp.clear');

    // Backward compatibility redirects for legacy URLs
    Route::redirect('/one-setting/legacy/gl', '/one-setting/gl');
    Route::redirect('/one-setting/legacy/unit-support', '/one-setting/unit-support');
    Route::redirect('/one-setting/legacy/dozer-pc', '/one-setting/dozer-pc');
    Route::redirect('/one-setting/legacy/pw-cp', '/one-setting/pw-cp');

    Route::get('/one-procedure', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard');
    Route::get('/one-procedure/dashboard', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard-index');
    
    // 1. Prosedur Routes
    Route::get('/one-procedure/prosedur', [OneProcedureController::class, 'prosedur'])->name('one-procedure.prosedur');
    Route::get('/one-procedure/prosedur/view/{id}', [OneProcedureController::class, 'viewDocument'])->name('one-procedure.prosedur.view');
    Route::post('/one-procedure/prosedur/store', [OneProcedureController::class, 'storeProcedure'])->name('one-procedure.prosedur.store');
    Route::post('/one-procedure/prosedur/update/{id}', [OneProcedureController::class, 'updateProcedure'])->name('one-procedure.prosedur.update');
    Route::delete('/one-procedure/prosedur/delete/{id}', [OneProcedureController::class, 'deleteProcedure'])->name('one-procedure.prosedur.delete');
    Route::post('/one-procedure/prosedur/category/store', [OneProcedureController::class, 'storeCategory'])->name('one-procedure.prosedur.category.store');
    Route::delete('/one-procedure/prosedur/category/delete/{id}', [OneProcedureController::class, 'deleteCategory'])->name('one-procedure.prosedur.category.delete');
    Route::post('/one-procedure/prosedur/type/store', [OneProcedureController::class, 'storeType'])->name('one-procedure.prosedur.type.store');
    Route::delete('/one-procedure/prosedur/type/delete/{id}', [OneProcedureController::class, 'deleteType'])->name('one-procedure.prosedur.type.delete');
    
    // 2. Activity Routes
    Route::get('/one-procedure/activity', [OneProcedureController::class, 'activity'])->name('one-procedure.activity');
    Route::post('/one-procedure/activity/category/sync-docs', [OneProcedureController::class, 'syncCategoryDocs'])->name('one-procedure.activity.category.sync-docs');
    
    // 3. Evaluasi Prosedur Routes
    Route::get('/one-procedure/evaluasi', [OneProcedureController::class, 'evaluasi'])->name('one-procedure.evaluasi');
    Route::get('/one-procedure/evaluasi/procedure/view/{id}', [OneProcedureController::class, 'viewEvalProcedure'])->name('one-procedure.evaluasi.procedure.view');
    Route::post('/one-procedure/evaluasi/procedure/store', [OneProcedureController::class, 'storeEvalProcedure'])->name('one-procedure.evaluasi.procedure.store');
    Route::post('/one-procedure/evaluasi/procedure/update/{procedure}', [OneProcedureController::class, 'updateEvalProcedure'])->name('one-procedure.evaluasi.procedure.update');
    Route::delete('/one-procedure/evaluasi/procedure/delete/{procedure}', [OneProcedureController::class, 'deleteEvalProcedure'])->name('one-procedure.evaluasi.procedure.delete');
    
    // Assessment routes inside Evaluasi
    Route::prefix('one-procedure/evaluasi/assessment')->name('one-procedure.evaluasi.assessment.')->controller(ProcedureAssessmentController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{assessment}', 'update')->name('update');
        Route::delete('/{assessment}', 'destroy')->name('destroy');
        Route::post('/{assessment}/publish', 'publish')->name('publish');
        Route::post('/{assessment}/retrigger', 'retrigger')->name('retrigger');
        Route::post('/{assessment}/archive', 'archive')->name('archive');
        Route::put('/{assessment}/targets', 'saveTargets')->name('targets');
        Route::post('/{assessment}/sign', 'sign')->name('sign');
        Route::post('/{assessment}/questions', 'storeQuestion')->name('questions.store');
        Route::put('/questions/{question}', 'updateQuestion')->name('questions.update');
        Route::delete('/questions/{question}', 'destroyQuestion')->name('questions.destroy');
    });
    
    // Monitoring & Certification routes
    Route::post('/one-procedure/evaluasi/store', [OneProcedureController::class, 'storeEvaluation'])->name('one-procedure.evaluasi.store');
    Route::prefix('one-procedure/evaluasi/monitoring')->name('one-procedure.evaluasi.monitoring.')->controller(ProcedureMonitoringController::class)->group(function () {
        Route::get('/export/excel', 'exportExcel')->name('export-excel');
        Route::get('/export/pdf', 'exportPdf')->name('export-pdf');
        Route::get('/{evaluation}', 'show')->name('show');
        Route::post('/{evaluation}/remedial', 'grantRemedial')->name('remedial');
        Route::post('/{evaluation}/coaching', 'scheduleCoaching')->name('coaching');
        Route::post('/followup/{followup}/grant', 'grantFollowupRemedial')->name('followup-grant');
        Route::delete('/{assessment}/{nrp}', 'destroy')->name('destroy');
    });
    Route::prefix('one-procedure/evaluasi/certificates')->name('one-procedure.evaluasi.certificates.')->controller(ProcedureCertificateController::class)->group(function () {
        Route::post('/issue-all', 'issueAll')->name('issue-all');
        Route::post('/reminders', 'sendReminders')->name('reminders');
        Route::post('/{certificate}/revoke', 'revoke')->name('revoke');
        Route::get('/{code}/pdf', 'pdf')->name('pdf');
    });

    // Assessment Saya: karyawan mengerjakan assessment yang ditugaskan (padanan /assessment/*.php di v1)
    Route::prefix('one-procedure/assessment')->name('one-procedure.my-assessment.')->controller(MyAssessmentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{assessment}/read', 'read')->name('read');
        Route::post('/{assessment}/start', 'start')->name('start');
        Route::get('/evaluation/{evaluation}', 'quiz')->name('quiz');
        Route::post('/evaluation/{evaluation}/autosave', 'autosave')->name('autosave');
        Route::post('/evaluation/{evaluation}/submit', 'submit')->name('submit');
        Route::get('/evaluation/{evaluation}/result', 'result')->name('result');
    });

    // SPDK Integration Routes
    Route::post('/one-power/spdk-lookup', [SpDkController::class, 'lookup'])->name('spdk.lookup');
    Route::get('/admin/spdk/sync-log', [SpDkController::class, 'syncLog'])->name('spdk.sync-log');

    // Internal Messages E2E Chat Routes
    Route::get('/internal-messages/contacts', [InternalMessageController::class, 'contacts'])->name('internal-messages.contacts');
    Route::get('/internal-messages/unread-count', [InternalMessageController::class, 'unreadCount'])->name('internal-messages.unread-count');
    Route::get('/internal-messages/{contactId}', [InternalMessageController::class, 'getMessages'])->name('internal-messages.get');
    Route::post('/internal-messages', [InternalMessageController::class, 'store'])->name('internal-messages.store');

    // Job Monitoring Routes (Admin Only)
    Route::middleware(['auth', 'admin'])->prefix('admin/jobs')->name('admin.jobs.')->group(function () {
        Route::get('/', [JobMonitorController::class, 'index'])->name('index');
        Route::post('/{jobName}/run', [JobMonitorController::class, 'manualTrigger'])->name('run');
        Route::get('/history', [JobMonitorController::class, 'history'])->name('history');
    });

    // Profil Routes
    Route::get('/profil', [PortalController::class, 'profil'])->name('profil');
    Route::put('/profil', [PortalController::class, 'updateProfile'])->name('profil.update');
    Route::post('/profil/foto', [PortalController::class, 'updateFoto'])->name('profil.foto.update');
    Route::delete('/profil/foto', [PortalController::class, 'destroyFoto'])->name('profil.foto.destroy');
    Route::post('/profil/password', [PortalController::class, 'updatePassword'])->name('profil.password.update');
    Route::put('/profil/kontak', [PortalController::class, 'updateKontak'])->name('profil.kontak.update');

    // Announcement Routes
    Route::get('/pengumuman', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/active', [\App\Http\Controllers\AnnouncementController::class, 'active'])->name('pengumuman.active');
    Route::post('/pengumuman', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('pengumuman.store');
    Route::patch('/pengumuman/{announcement}/visibility', [\App\Http\Controllers\AnnouncementController::class, 'updateVisibility'])->name('pengumuman.update-visibility');
    Route::post('/pengumuman/{announcement}/seen', [\App\Http\Controllers\AnnouncementController::class, 'markSeen'])->name('pengumuman.mark-seen');
    Route::delete('/pengumuman/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('pengumuman.destroy');

    // Reward Center Routes
    Route::get('/rewards', [\App\Http\Controllers\RewardController::class, 'index'])->name('rewards.index');
    Route::post('/rewards', [\App\Http\Controllers\RewardController::class, 'store'])->name('rewards.store');
    Route::post('/rewards/{reward}/seen', [\App\Http\Controllers\RewardController::class, 'markSeen'])->name('rewards.mark-seen');
    Route::delete('/rewards/{reward}', [\App\Http\Controllers\RewardController::class, 'destroy'])->name('rewards.destroy');

    foreach (glob(base_path('routes/one-roster/*.php')) as $oneRosterRoutes) {
        require $oneRosterRoutes;
    }

    $implementedModuleKeys = ['one-power', 'one-academy', 'one-core', 'one-dms', 'one-gmp', 'one-reporting', 'one-setting', 'one-procedure', 'one-roster'];

    $moduleKeys = collect(config('modules.list'))
        ->pluck('key')
        ->reject(fn (string $key) => in_array($key, $implementedModuleKeys, true))
        ->implode('|');

    if (! empty($moduleKeys)) {
        Route::get('/{module}', [ModuleController::class, 'show'])
            ->where('module', $moduleKeys)
            ->name('module.show');
    }
});
