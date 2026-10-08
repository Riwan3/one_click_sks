<?php

use App\Http\Controllers\OneRosterMoncuCalendarController;
use App\Http\Controllers\OneRosterMoncuController;
use App\Http\Controllers\OneRosterMoncuMonitoringController;
use Illuminate\Support\Facades\Route;

// MONCU (input cuti)
Route::prefix('one-roster/moncu')->name('one-roster.moncu.')->group(function () {
    Route::get('/', [OneRosterMoncuController::class, 'index'])->name('index');
    Route::post('employees', [OneRosterMoncuController::class, 'storeEmployee'])->name('employees.store');
    Route::get('employees/search', [OneRosterMoncuController::class, 'searchEmployees'])->name('employees.search');
    Route::get('periods/preview', [OneRosterMoncuController::class, 'previewPeriod'])->name('periods.preview');
    Route::post('periods', [OneRosterMoncuController::class, 'storePeriod'])->name('periods.store');
    Route::delete('periods', [OneRosterMoncuController::class, 'destroyPeriod'])->name('periods.destroy');
    Route::post('sync', [OneRosterMoncuController::class, 'sync'])->name('sync');
    Route::post('sheet-import/preview', [OneRosterMoncuController::class, 'sheetPreview'])->name('sheet-import.preview');
    Route::post('sheet-import/commit', [OneRosterMoncuController::class, 'sheetCommit'])->name('sheet-import.commit');
});

// MONCU Monitoring & MONCU Calendar (read-only)
Route::get('one-roster/moncu-monitoring', [OneRosterMoncuMonitoringController::class, 'index'])->name('one-roster.moncu-monitoring.index');
Route::get('one-roster/moncu-monitoring/detail', [OneRosterMoncuMonitoringController::class, 'detail'])->name('one-roster.moncu-monitoring.detail');
Route::get('one-roster/moncu-calendar', [OneRosterMoncuCalendarController::class, 'index'])->name('one-roster.moncu-calendar.index');
Route::get('one-roster/moncu-calendar/day', [OneRosterMoncuCalendarController::class, 'day'])->name('one-roster.moncu-calendar.day');
