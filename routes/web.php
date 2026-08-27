<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\CoachingClinicController;
use App\Http\Controllers\CoachingClinicDashboardController;
use App\Http\Controllers\CoachingClinicExportController;
use App\Http\Controllers\CoachingCounselingController;
use App\Http\Controllers\CoachingMateriController;
use App\Http\Controllers\CoachingProgressWizardController;
use App\Http\Controllers\CoachingTahapanController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FatigueInterviewController;
use App\Http\Controllers\HmDataController;
use App\Http\Controllers\IzinKaryawanController;
use App\Http\Controllers\ManpowerPerformanceReportController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\MorDataController;
use App\Http\Controllers\MppDepartemenController;
use App\Http\Controllers\MppDashboardController;
use App\Http\Controllers\MppDetailController;
use App\Http\Controllers\MppMasterCategoryController;
use App\Http\Controllers\MppPhotoController;
use App\Http\Controllers\MppReportController;
use App\Http\Controllers\OneAcademyController;
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
use App\Http\Controllers\PortalController;
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
use Illuminate\Support\Facades\Route;

// Public Health Check Endpoint (for monitoring systems)
Route::get('/health', [HealthController::class, 'check'])->name('health.check');

Route::get('/', function () {
    return auth()->check() ? redirect('/portal') : redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::get('/portal/data', [PortalController::class, 'mobileData'])->name('mobile.data');
    Route::get('/portal/aktivity', [PortalController::class, 'aktivity'])->name('mobile.aktivity');
    Route::get('/portal/profil', [PortalController::class, 'profil'])->name('mobile.profil');

    Route::get('/one-power', [OnePowerController::class, 'dashboard'])->name('one-power.dashboard');
    Route::get('/one-power/employees', [EmployeeController::class, 'index'])->name('one-power.employees.index');
    Route::get('/one-power/employees/inactive', [EmployeeController::class, 'inactive'])->name('one-power.employees.inactive');
    Route::get('/one-power/employees/create', [EmployeeController::class, 'create'])->name('one-power.employees.create');
    Route::post('/one-power/employees', [EmployeeController::class, 'store'])->name('one-power.employees.store');
    Route::get('/one-power/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('one-power.employees.edit');
    Route::put('/one-power/employees/{employee}', [EmployeeController::class, 'update'])->name('one-power.employees.update');
    Route::delete('/one-power/employees/{employee}', [EmployeeController::class, 'destroy'])->name('one-power.employees.destroy');
    Route::post('/one-power/employees/{employee}/status', [EmployeeController::class, 'setStatus'])->name('one-power.employees.status');

    Route::get('/one-power/personal-data/hm', [HmDataController::class, 'index'])->name('one-power.hm-data.index');
    Route::get('/one-power/personal-data/hm/search-employee', [HmDataController::class, 'searchEmployee'])->name('one-power.hm-data.search-employee');
    Route::post('/one-power/personal-data/hm', [HmDataController::class, 'store'])->name('one-power.hm-data.store');
    Route::put('/one-power/personal-data/hm/{hm}', [HmDataController::class, 'update'])->name('one-power.hm-data.update');
    Route::delete('/one-power/personal-data/hm/{hm}', [HmDataController::class, 'destroy'])->name('one-power.hm-data.destroy');
    Route::post('/one-power/personal-data/hm/import', [HmDataController::class, 'import'])->name('one-power.hm-data.import');

    Route::get('/one-power/personal-data/attendance', [AttendanceController::class, 'index'])->name('one-power.attendance.index');
    Route::get('/one-power/personal-data/attendance/search-employee', [AttendanceController::class, 'searchEmployee'])->name('one-power.attendance.search-employee');
    Route::get('/one-power/personal-data/attendance/template', [AttendanceController::class, 'downloadTemplate'])->name('one-power.attendance.template');
    Route::post('/one-power/personal-data/attendance/import', [AttendanceController::class, 'import'])->name('one-power.attendance.import');
    Route::post('/one-power/personal-data/attendance', [AttendanceController::class, 'store'])->name('one-power.attendance.store');
    Route::put('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'update'])->name('one-power.attendance.update');
    Route::delete('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('one-power.attendance.destroy');

    Route::get('/one-power/personal-data/mpr', [ManpowerPerformanceReportController::class, 'index'])->name('one-power.mpr.index');
    Route::get('/one-power/personal-data/mpr/search-employee', [ManpowerPerformanceReportController::class, 'searchEmployee'])->name('one-power.mpr.search-employee');
    // MPP Departemen Phase 1
    Route::get('/one-power/mpp-departemen', [MppDashboardController::class, 'index'])->name('one-power.mpp-departemen');

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
    Route::get('/one-power/quartal-operator-report/template', [QuartalOperatorReportController::class, 'downloadTemplate'])->name('one-power.quartal-operator-report.template');
    Route::post('/one-power/quartal-operator-report/import', [QuartalOperatorReportController::class, 'import'])->name('one-power.quartal-operator-report.import');
    Route::post('/one-power/quartal-operator-report/recalculate', [QuartalOperatorReportController::class, 'recalculate'])->name('one-power.quartal-operator-report.recalculate');

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
    Route::get('/one-power/roster-personal', [RosterPersonalController::class, 'index'])->name('one-power.roster-personal.index');
    Route::get('/one-power/roster-personal/search-employee', [RosterPersonalController::class, 'searchEmployee'])->name('one-power.roster-personal.search-employee');
    Route::get('/one-power/roster', [RosterController::class, 'index'])->name('one-power.roster.index');
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

    Route::get('/one-power/pembinaan/berita-acara', [BeritaAcaraController::class, 'index'])->name('one-power.berita-acara.index');
    Route::get('/one-power/pembinaan/berita-acara/search-employee', [BeritaAcaraController::class, 'searchEmployee'])->name('one-power.berita-acara.search-employee');
    Route::post('/one-power/pembinaan/berita-acara/import', [BeritaAcaraController::class, 'import'])->name('one-power.berita-acara.import');
    Route::get('/one-power/pembinaan/berita-acara/template', [BeritaAcaraController::class, 'downloadTemplate'])->name('one-power.berita-acara.template');
    Route::get('/one-power/pembinaan/berita-acara/create', [BeritaAcaraController::class, 'create'])->name('one-power.berita-acara.create');
    Route::post('/one-power/pembinaan/berita-acara', [BeritaAcaraController::class, 'store'])->name('one-power.berita-acara.store');
    Route::get('/one-power/pembinaan/berita-acara/{beritaAcara}/edit', [BeritaAcaraController::class, 'edit'])->name('one-power.berita-acara.edit');
    Route::put('/one-power/pembinaan/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'update'])->name('one-power.berita-acara.update');
    Route::delete('/one-power/pembinaan/berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'destroy'])->name('one-power.berita-acara.destroy');
    Route::get('/one-power/pembinaan/berita-acara/{beritaAcara}/print', [BeritaAcaraController::class, 'show'])->name('one-power.berita-acara.print');

    Route::get('/one-power/pembinaan/coaching-counseling', [CoachingCounselingController::class, 'index'])->name('one-power.coaching-counseling.index');
    Route::post('/one-power/pembinaan/coaching-counseling/import', [CoachingCounselingController::class, 'import'])->name('one-power.coaching-counseling.import');
    Route::get('/one-power/pembinaan/coaching-counseling/template', [CoachingCounselingController::class, 'downloadTemplate'])->name('one-power.coaching-counseling.template');
    Route::get('/one-power/pembinaan/coaching-counseling/search-employee', [CoachingCounselingController::class, 'searchEmployee'])->name('one-power.coaching-counseling.search-employee');
    Route::get('/one-power/pembinaan/coaching-counseling/create', [CoachingCounselingController::class, 'create'])->name('one-power.coaching-counseling.create');
    Route::post('/one-power/pembinaan/coaching-counseling', [CoachingCounselingController::class, 'store'])->name('one-power.coaching-counseling.store');
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/edit', [CoachingCounselingController::class, 'edit'])->name('one-power.coaching-counseling.edit');
    Route::put('/one-power/pembinaan/coaching-counseling/{coachingCounseling}', [CoachingCounselingController::class, 'update'])->name('one-power.coaching-counseling.update');
    Route::delete('/one-power/pembinaan/coaching-counseling/{coachingCounseling}', [CoachingCounselingController::class, 'destroy'])->name('one-power.coaching-counseling.destroy');

    Route::get('/one-power/pembinaan/interview-fatigue', [FatigueInterviewController::class, 'index'])->name('one-power.fatigue-interview.index');
    Route::get('/one-power/pembinaan/interview-fatigue/search-employee', [FatigueInterviewController::class, 'searchEmployee'])->name('one-power.fatigue-interview.search-employee');
    Route::get('/one-power/pembinaan/interview-fatigue/create', [FatigueInterviewController::class, 'create'])->name('one-power.fatigue-interview.create');
    Route::post('/one-power/pembinaan/interview-fatigue', [FatigueInterviewController::class, 'store'])->name('one-power.fatigue-interview.store');
    Route::get('/one-power/pembinaan/interview-fatigue/{fatigueInterview}/edit', [FatigueInterviewController::class, 'edit'])->name('one-power.fatigue-interview.edit');
    Route::put('/one-power/pembinaan/interview-fatigue/{fatigueInterview}', [FatigueInterviewController::class, 'update'])->name('one-power.fatigue-interview.update');
    Route::delete('/one-power/pembinaan/interview-fatigue/{fatigueInterview}', [FatigueInterviewController::class, 'destroy'])->name('one-power.fatigue-interview.destroy');
    Route::get('/one-power/pembinaan/interview-fatigue/{fatigueInterview}/print', [FatigueInterviewController::class, 'show'])->name('one-power.fatigue-interview.print');

    Route::get('/one-power/pembinaan/surat-teguran', [SuratTeguranController::class, 'index'])->name('one-power.surat-teguran.index');
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
    Route::get('/one-power/pembinaan/pemulangan-karyawan/search-employee', [PemulanganKaryawanController::class, 'searchEmployee'])->name('one-power.pemulangan-karyawan.search-employee');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/create', [PemulanganKaryawanController::class, 'create'])->name('one-power.pemulangan-karyawan.create');
    Route::post('/one-power/pembinaan/pemulangan-karyawan', [PemulanganKaryawanController::class, 'store'])->name('one-power.pemulangan-karyawan.store');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}/edit', [PemulanganKaryawanController::class, 'edit'])->name('one-power.pemulangan-karyawan.edit');
    Route::put('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}', [PemulanganKaryawanController::class, 'update'])->name('one-power.pemulangan-karyawan.update');
    Route::delete('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}', [PemulanganKaryawanController::class, 'destroy'])->name('one-power.pemulangan-karyawan.destroy');
    Route::get('/one-power/pembinaan/pemulangan-karyawan/{pemulanganKaryawan}/print', [PemulanganKaryawanController::class, 'show'])->name('one-power.pemulangan-karyawan.print');

    Route::get('/one-power/pembinaan/pakta-integritas', [PaktaIntegritasController::class, 'index'])->name('one-power.pakta-integritas.index');
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
    Route::get('/one-power/pembinaan/validasi-istirahat/search-employee', [ValidasiIstirahatController::class, 'searchEmployee'])->name('one-power.validasi-istirahat.search-employee');
    Route::get('/one-power/pembinaan/validasi-istirahat/create', [ValidasiIstirahatController::class, 'create'])->name('one-power.validasi-istirahat.create');
    Route::post('/one-power/pembinaan/validasi-istirahat', [ValidasiIstirahatController::class, 'store'])->name('one-power.validasi-istirahat.store');
    Route::get('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}/edit', [ValidasiIstirahatController::class, 'edit'])->name('one-power.validasi-istirahat.edit');
    Route::put('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}', [ValidasiIstirahatController::class, 'update'])->name('one-power.validasi-istirahat.update');
    Route::delete('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}', [ValidasiIstirahatController::class, 'destroy'])->name('one-power.validasi-istirahat.destroy');
    Route::get('/one-power/pembinaan/validasi-istirahat/{validasiIstirahat}/print', [ValidasiIstirahatController::class, 'show'])->name('one-power.validasi-istirahat.print');

    Route::get('/one-power/pembinaan/pemanggilan-karyawan', [PemanggilanKaryawanController::class, 'index'])->name('one-power.pemanggilan-karyawan.index');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/search-employee', [PemanggilanKaryawanController::class, 'searchEmployee'])->name('one-power.pemanggilan-karyawan.search-employee');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/create', [PemanggilanKaryawanController::class, 'create'])->name('one-power.pemanggilan-karyawan.create');
    Route::post('/one-power/pembinaan/pemanggilan-karyawan', [PemanggilanKaryawanController::class, 'store'])->name('one-power.pemanggilan-karyawan.store');
    Route::get('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}/edit', [PemanggilanKaryawanController::class, 'edit'])->name('one-power.pemanggilan-karyawan.edit');
    Route::put('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}', [PemanggilanKaryawanController::class, 'update'])->name('one-power.pemanggilan-karyawan.update');
    Route::delete('/one-power/pembinaan/pemanggilan-karyawan/{pemanggilanKaryawan}', [PemanggilanKaryawanController::class, 'destroy'])->name('one-power.pemanggilan-karyawan.destroy');

    Route::get('/one-power/pembinaan/surat-peringatan', [SuratPeringatanController::class, 'index'])->name('one-power.surat-peringatan.index');
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

    Route::get('/one-core', [OneCoreController::class, 'dashboard'])->name('one-core.dashboard');
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

    Route::get('/one-dms', [OneDmsController::class, 'dashboard'])->name('one-dms.dashboard');

    Route::get('/one-gmp', [OneGmpController::class, 'dashboard'])->name('one-gmp.dashboard');

    Route::get('/one-reporting', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard');

    Route::get('/one-setting', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard');

    Route::get('/one-procedure', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard');

    // SPDK Integration Routes
    Route::post('/one-power/spdk-lookup', [SpDkController::class, 'lookup'])->name('spdk.lookup');
    Route::get('/admin/spdk/sync-log', [SpDkController::class, 'syncLog'])->name('spdk.sync-log');

    // Job Monitoring Routes (Admin Only)
    Route::middleware(['auth', 'admin'])->prefix('admin/jobs')->name('admin.jobs.')->group(function () {
        Route::get('/', [JobMonitorController::class, 'index'])->name('index');
        Route::post('/{jobName}/run', [JobMonitorController::class, 'manualTrigger'])->name('run');
        Route::get('/history', [JobMonitorController::class, 'history'])->name('history');
    });

    $implementedModuleKeys = ['one-power', 'one-academy', 'one-core', 'one-dms', 'one-gmp', 'one-reporting', 'one-setting', 'one-procedure'];

    $moduleKeys = collect(config('modules.list'))
    ->pluck('key')
    ->reject(fn (string $key) => in_array($key, $implementedModuleKeys, true))
    ->implode('|');

    Route::get('/{module}', [ModuleController::class, 'show'])
    ->where('module', $moduleKeys)
    ->name('module.show');

});
