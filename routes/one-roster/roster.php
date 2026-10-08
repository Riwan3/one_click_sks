<?php

use App\Http\Controllers\OneRosterController;
use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. Izin lihat/kelola dicek di Form Request & RosterV2Service.
Route::prefix('one-roster/roster')->name('one-roster.roster.')->controller(OneRosterController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');

    Route::get('employees/search', 'searchEmployee')->name('employees.search');
    Route::get('import-template', 'importTemplate')->name('import-template');
    Route::post('sheet-import/preview', 'sheetPreview')->name('sheet-import.preview');
    Route::post('sheet-import/commit', 'sheetCommit')->name('sheet-import.commit');

    Route::prefix('{id}')->whereNumber('id')->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('upload/download', 'downloadUpload')->name('upload.download');
        Route::post('update', 'update')->name('update');
        Route::post('status', 'status')->name('status');
        Route::post('duplicate', 'duplicate')->name('duplicate');
        Route::post('visibility', 'saveVisibility')->name('visibility');
        Route::post('moncu-sync', 'syncMoncu')->name('moncu-sync');

        Route::post('sections', 'storeSection')->name('sections.store');
        Route::delete('sections/{section}', 'destroySection')->whereNumber('section')->name('sections.destroy');
        Route::post('sections/{section}/target', 'saveSectionTarget')->whereNumber('section')->name('sections.target');
        Route::post('sections/{section}/members', 'storeMember')->whereNumber('section')->name('members.store');

        Route::post('members/{member}/move', 'moveMember')->whereNumber('member')->name('members.move');
        Route::post('members/{member}/rotation', 'saveMemberRotation')->whereNumber('member')->name('members.rotation');
        Route::post('members/{member}/unit', 'saveMemberUnit')->whereNumber('member')->name('members.unit');
        Route::delete('members/{member}', 'destroyMember')->whereNumber('member')->name('members.destroy');

        Route::post('cells', 'setCell')->name('cells.set');
        Route::post('cells/fill', 'fillCells')->name('cells.fill');

        Route::post('details', 'storeDetail')->name('details.store');
        Route::put('details/{detail}', 'updateDetail')->whereNumber('detail')->name('details.update');
        Route::delete('details/{detail}', 'destroyDetail')->whereNumber('detail')->name('details.destroy');
        Route::post('details/{detail}/allocations', 'storeAllocation')->whereNumber('detail')->name('allocations.store');
        Route::delete('details/{detail}/allocations', 'destroyAllocation')->whereNumber('detail')->name('allocations.destroy');

        Route::post('import/preview', 'importPreview')->name('import.preview');
        Route::post('import/commit', 'importCommit')->name('import.commit');
    });
});
