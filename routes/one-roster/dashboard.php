<?php

use App\Http\Controllers\OneRosterAnalisisController;
use App\Http\Controllers\OneRosterDashboardController;
use App\Http\Controllers\OneRosterProduksiController;
use App\Http\Controllers\OneRosterShareController;
use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. Dashboard, Monitoring Departement, Analisis Harian, Share Roster.
Route::get('/one-roster/dashboard', [OneRosterDashboardController::class, 'index'])->name('one-roster.dashboard');

Route::get('/one-roster/monitoring-departemen', [OneRosterProduksiController::class, 'index'])->name('one-roster.produksi');

Route::get('/one-roster/analisis-harian', [OneRosterAnalisisController::class, 'index'])->name('one-roster.analisis');
Route::get('/one-roster/analisis-harian/data', [OneRosterAnalisisController::class, 'data'])->name('one-roster.analisis.data');
Route::post('/one-roster/analisis-harian/target', [OneRosterAnalisisController::class, 'target'])->name('one-roster.analisis.target');

Route::get('/one-roster/share', [OneRosterShareController::class, 'index'])->name('one-roster.share');
