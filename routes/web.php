<?php

use App\Http\Controllers\InternalMessageController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceV2Controller;
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
use App\Http\Controllers\MonitoringGradeController;
use App\Http\Controllers\EvaluasiKompetensiController;
use App\Http\Controllers\BestEmployeeController;
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
use App\Http\Controllers\UnitController;
use App\Http\Controllers\NomorPentingController;
use App\Http\Controllers\ForgotPasswordRequestController;
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
    Route::post('/one-power/employees/import', [EmployeeController::class, 'import'])->name('one-power.employees.import');
    Route::get('/one-power/employees/template', [EmployeeController::class, 'downloadTemplate'])->name('one-power.employees.template');
    Route::get('/one-power/employees/export', [EmployeeController::class, 'export'])->name('one-power.employees.export');
    Route::post('/one-power/employees/bulk-delete-inactive', [EmployeeController::class, 'bulkDeleteInactive'])->name('one-power.employees.bulk-delete-inactive');
    Route::get('/one-power/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('one-power.employees.edit');
    Route::put('/one-power/employees/{employee}', [EmployeeController::class, 'update'])->name('one-power.employees.update');
    Route::delete('/one-power/employees/{employee}', [EmployeeController::class, 'destroy'])->name('one-power.employees.destroy');
    Route::post('/one-power/employees/{employee}/status', [EmployeeController::class, 'setStatus'])->name('one-power.employees.status');

    // Sahabat Pengusaha (GL - Mentee) & Print Profile
    Route::get('/one-power/employees/list-gl', [EmployeeController::class, 'listGL'])->name('one-power.employees.list-gl');
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
    Route::get('/one-power/personal-data/attendance/search-employee', [AttendanceController::class, 'searchEmployee'])->name('one-power.attendance.search-employee');
    Route::get('/one-power/personal-data/attendance/template', [AttendanceController::class, 'downloadTemplate'])->name('one-power.attendance.template');
    Route::post('/one-power/personal-data/attendance/import', [AttendanceController::class, 'import'])->name('one-power.attendance.import');
    Route::post('/one-power/personal-data/attendance', [AttendanceController::class, 'store'])->name('one-power.attendance.store');
    Route::put('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'update'])->name('one-power.attendance.update');
    Route::delete('/one-power/personal-data/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('one-power.attendance.destroy');

    Route::get('/one-power/personal-data/attendance-v2', [AttendanceV2Controller::class, 'index'])->name('one-power.attendance-v2.index');
    Route::post('/one-power/personal-data/attendance-v2/import-api', [AttendanceV2Controller::class, 'importFromApi'])->name('one-power.attendance-v2.import-api');
    Route::delete('/one-power/personal-data/attendance-v2/{attendanceV2}', [AttendanceV2Controller::class, 'destroy'])->name('one-power.attendance-v2.destroy');

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
    Route::get('/one-power/pembinaan/coaching-counseling/{coachingCounseling}/print', [CoachingCounselingController::class, 'show'])->name('one-power.coaching-counseling.print');

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
    Route::post('/one-academy/store-pengajuan', [OneAcademyController::class, 'storePengajuan'])->name('one-academy.store-pengajuan');
    Route::delete('/one-academy/delete-pengajuan/{id}', [OneAcademyController::class, 'deletePengajuan'])->name('one-academy.delete-pengajuan');
    Route::get('/one-academy/odp-database', [OneAcademyController::class, 'odpList'])->name('one-academy.odp-database');
    Route::post('/one-academy/store-odp-karyawan', [OneAcademyController::class, 'storeOdpKaryawan'])->name('one-academy.store-odp-karyawan');
    Route::get('/one-academy/monitoring-grade', [MonitoringGradeController::class, 'index'])->name('one-academy.monitoring-grade.index');
    Route::post('/one-academy/monitoring-grade/store-grade', [MonitoringGradeController::class, 'storeGrade'])->name('one-academy.monitoring-grade.store-grade');
    Route::post('/one-academy/monitoring-grade/store-versatility', [MonitoringGradeController::class, 'storeVersatility'])->name('one-academy.monitoring-grade.store-versatility');

    Route::get('/one-academy/evaluasi-kompetensi', [EvaluasiKompetensiController::class, 'index'])->name('one-academy.evaluasi-kompetensi.index');
    Route::post('/one-academy/evaluasi-kompetensi/store-materi', [EvaluasiKompetensiController::class, 'storeMateri'])->name('one-academy.evaluasi-kompetensi.store-materi');
    Route::post('/one-academy/evaluasi-kompetensi/store-assessment', [EvaluasiKompetensiController::class, 'storeAssessment'])->name('one-academy.evaluasi-kompetensi.store-assessment');
    Route::post('/one-academy/evaluasi-kompetensi/store-question', [EvaluasiKompetensiController::class, 'storeQuestion'])->name('one-academy.evaluasi-kompetensi.store-question');
    Route::post('/one-academy/evaluasi-kompetensi/save-targets', [EvaluasiKompetensiController::class, 'saveTargets'])->name('one-academy.evaluasi-kompetensi.save-targets');

    Route::get('/one-academy/best-employee', [BestEmployeeController::class, 'index'])->name('one-academy.best-employee.index');
    Route::post('/one-academy/best-employee/store-campaign', [BestEmployeeController::class, 'storeCampaign'])->name('one-academy.best-employee.store-campaign');
    Route::post('/one-academy/best-employee/store-question', [BestEmployeeController::class, 'storeQuestion'])->name('one-academy.best-employee.store-question');
    Route::post('/one-academy/best-employee/store-participant', [BestEmployeeController::class, 'storeParticipant'])->name('one-academy.best-employee.store-participant');
    Route::post('/one-academy/best-employee/reset-attempt', [BestEmployeeController::class, 'resetAttempt'])->name('one-academy.best-employee.reset-attempt');

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

    // Nomor Penting
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
    Route::post('/one-dms/store', [OneDmsController::class, 'storeDms'])->name('one-dms.store');
    Route::delete('/one-dms/delete/{id}', [OneDmsController::class, 'deleteDms'])->name('one-dms.delete');
    Route::post('/one-dms/save-petugas', [OneDmsController::class, 'savePetugas'])->name('one-dms.save-petugas');
    Route::get('/one-dms/data-icc/{category}', [OneDmsController::class, 'dataIcc'])->name('one-dms.data-icc');

    Route::get('/one-gmp', function () {
        return redirect()->route('one-gmp.dashboard');
    });
    Route::get('/one-gmp/dashboard', [OneGmpController::class, 'dashboard'])->name('one-gmp.dashboard');
    Route::get('/one-gmp/trend', [OneGmpController::class, 'trend'])->name('one-gmp.trend');
    Route::get('/one-gmp/area/{type}', [OneGmpController::class, 'area'])->name('one-gmp.area');
    Route::get('/one-gmp/penilaian/{type}', [OneGmpController::class, 'penilaian'])->name('one-gmp.penilaian');
    Route::post('/one-gmp/penilaian/store', [OneGmpController::class, 'storePenilaian'])->name('one-gmp.penilaian.store');
    Route::get('/one-gmp/approval', [OneGmpController::class, 'approval'])->name('one-gmp.approval');
    Route::post('/one-gmp/approval/approve/{id}', [OneGmpController::class, 'approveForm'])->name('one-gmp.approval.approve');
    Route::post('/one-gmp/approval/reject/{id}', [OneGmpController::class, 'rejectForm'])->name('one-gmp.approval.reject');
    Route::post('/one-gmp/approval/return/{id}', [OneGmpController::class, 'returnForm'])->name('one-gmp.approval.return');
    Route::get('/one-gmp/master', [OneGmpController::class, 'masterGmp'])->name('one-gmp.master');
    Route::post('/one-gmp/master/store', [OneGmpController::class, 'storeMaster'])->name('one-gmp.master.store');
    Route::delete('/one-gmp/master/delete/{id}', [OneGmpController::class, 'deleteMaster'])->name('one-gmp.master.delete');

    Route::get('/one-reporting', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard');
    Route::get('/one-reporting/dashboard', [OneReportingController::class, 'dashboard'])->name('one-reporting.dashboard-index');
    Route::get('/one-reporting/daily', [OneReportingController::class, 'daily'])->name('one-reporting.daily');
    Route::post('/one-reporting/daily/task-toggle', [OneReportingController::class, 'updateTask'])->name('one-reporting.daily.task-toggle');
    Route::post('/one-reporting/daily/store', [OneReportingController::class, 'storeAssignment'])->name('one-reporting.daily.store');
    Route::delete('/one-reporting/daily/delete/{id}', [OneReportingController::class, 'deleteAssignment'])->name('one-reporting.daily.delete');
    
    Route::get('/one-reporting/laporan', [OneReportingController::class, 'laporan'])->name('one-reporting.laporan');
    Route::get('/one-reporting/laporan/export', [OneReportingController::class, 'exportCsv'])->name('one-reporting.laporan.export');
    
    Route::get('/one-reporting/master', [OneReportingController::class, 'master'])->name('one-reporting.master');
    Route::post('/one-reporting/master/area/store', [OneReportingController::class, 'storeArea'])->name('one-reporting.master.area.store');
    Route::post('/one-reporting/master/area/update/{id}', [OneReportingController::class, 'updateArea'])->name('one-reporting.master.area.update');
    Route::delete('/one-reporting/master/area/delete/{id}', [OneReportingController::class, 'deleteArea'])->name('one-reporting.master.area.delete');
    Route::post('/one-reporting/master/unit/store', [OneReportingController::class, 'storeUnit'])->name('one-reporting.master.unit.store');
    Route::post('/one-reporting/master/unit/update/{id}', [OneReportingController::class, 'updateUnit'])->name('one-reporting.master.unit.update');
    Route::delete('/one-reporting/master/unit/delete/{id}', [OneReportingController::class, 'deleteUnit'])->name('one-reporting.master.unit.delete');
    Route::post('/one-reporting/master/aktivitas/store', [OneReportingController::class, 'storeAktivitas'])->name('one-reporting.master.aktivitas.store');
    Route::post('/one-reporting/master/aktivitas/update/{id}', [OneReportingController::class, 'updateAktivitas'])->name('one-reporting.master.aktivitas.update');
    Route::delete('/one-reporting/master/aktivitas/delete/{id}', [OneReportingController::class, 'deleteAktivitas'])->name('one-reporting.master.aktivitas.delete');
    
    Route::get('/one-reporting/ojk', [OneReportingController::class, 'ojk'])->name('one-reporting.ojk');
    Route::post('/one-reporting/ojk/upload', [OneReportingController::class, 'uploadOjk'])->name('one-reporting.ojk.upload');
    Route::delete('/one-reporting/ojk/delete/{id}', [OneReportingController::class, 'deleteOjk'])->name('one-reporting.ojk.delete');

    Route::get('/one-setting', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard');
    Route::get('/one-setting/dashboard', [OneSettingController::class, 'dashboard'])->name('one-setting.dashboard-index');
    Route::get('/one-setting/settingan', [OneSettingController::class, 'settingan'])->name('one-setting.settingan');
    
    // Manpower & Roster Actions
    Route::post('/one-setting/roster/manual', [OneSettingController::class, 'storeManualRoster'])->name('one-setting.roster.manual');
    Route::post('/one-setting/roster/update/{id}', [OneSettingController::class, 'updateRoster'])->name('one-setting.roster.update');
    Route::delete('/one-setting/roster/delete/{id}', [OneSettingController::class, 'deleteRoster'])->name('one-setting.roster.delete');
    Route::post('/one-setting/roster/clear', [OneSettingController::class, 'clearRoster'])->name('one-setting.roster.clear');
    Route::post('/one-setting/manpower/sync-gsheet', [OneSettingController::class, 'syncGsheetManpower'])->name('one-setting.manpower.sync-gsheet');
    
    // Fleet Actions
    Route::post('/one-setting/unit-fleet/store', [OneSettingController::class, 'storeUnitFleet'])->name('one-setting.unit-fleet.store');
    Route::delete('/one-setting/unit-fleet/delete/{id}', [OneSettingController::class, 'deleteUnitFleet'])->name('one-setting.unit-fleet.delete');
    Route::post('/one-setting/fleet/import', [OneSettingController::class, 'importFleet'])->name('one-setting.fleet.import');
    Route::post('/one-setting/fleet/delete', [OneSettingController::class, 'deleteFleetData'])->name('one-setting.fleet.delete');
    
    // Monitoring Views
    Route::get('/one-setting/monitoring-manpower', [OneSettingController::class, 'monitoringManpower'])->name('one-setting.monitoring-manpower');
    Route::get('/one-setting/monitoring-fleet', [OneSettingController::class, 'monitoringFleet'])->name('one-setting.monitoring-fleet');

    Route::get('/one-procedure', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard');
    Route::get('/one-procedure/dashboard', [OneProcedureController::class, 'dashboard'])->name('one-procedure.dashboard-index');
    
    // 1. Prosedur Routes
    Route::get('/one-procedure/prosedur', [OneProcedureController::class, 'prosedur'])->name('one-procedure.prosedur');
    Route::post('/one-procedure/prosedur/store', [OneProcedureController::class, 'storeProcedure'])->name('one-procedure.prosedur.store');
    Route::post('/one-procedure/prosedur/update/{id}', [OneProcedureController::class, 'updateProcedure'])->name('one-procedure.prosedur.update');
    Route::delete('/one-procedure/prosedur/delete/{id}', [OneProcedureController::class, 'deleteProcedure'])->name('one-procedure.prosedur.delete');
    
    // 2. Activity Routes
    Route::get('/one-procedure/activity', [OneProcedureController::class, 'activity'])->name('one-procedure.activity');
    Route::post('/one-procedure/assessment/store', [OneProcedureController::class, 'storeAssessment'])->name('one-procedure.assessment.store');
    Route::post('/one-procedure/assessment/update/{id}', [OneProcedureController::class, 'updateAssessment'])->name('one-procedure.assessment.update');
    Route::delete('/one-procedure/assessment/delete/{id}', [OneProcedureController::class, 'deleteAssessment'])->name('one-procedure.assessment.delete');
    Route::post('/one-procedure/question/store', [OneProcedureController::class, 'storeQuestion'])->name('one-procedure.question.store');
    Route::delete('/one-procedure/question/delete/{id}', [OneProcedureController::class, 'deleteQuestion'])->name('one-procedure.question.delete');
    
    // 3. Evaluasi Prosedur Routes
    Route::get('/one-procedure/evaluasi', [OneProcedureController::class, 'evaluasi'])->name('one-procedure.evaluasi');
    Route::post('/one-procedure/evaluasi/store', [OneProcedureController::class, 'storeEvaluation'])->name('one-procedure.evaluasi.store');
    Route::delete('/one-procedure/evaluasi/delete/{id}', [OneProcedureController::class, 'deleteEvaluation'])->name('one-procedure.evaluasi.delete');

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

    // Profil Route Shortcut
    Route::get('/profil', [PortalController::class, 'profil'])->name('profil');

    // Announcement Routes
    Route::get('/pengumuman', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('pengumuman.index');
    Route::post('/pengumuman', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('pengumuman.store');
    Route::delete('/pengumuman/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('pengumuman.destroy');

    // Reward Center Routes
    Route::get('/rewards', [\App\Http\Controllers\RewardController::class, 'index'])->name('rewards.index');
    Route::post('/rewards', [\App\Http\Controllers\RewardController::class, 'store'])->name('rewards.store');
    Route::delete('/rewards/{reward}', [\App\Http\Controllers\RewardController::class, 'destroy'])->name('rewards.destroy');

    $implementedModuleKeys = ['one-power', 'one-academy', 'one-core', 'one-dms', 'one-gmp', 'one-reporting', 'one-setting', 'one-procedure'];

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
