<?php

use App\Http\Controllers\OneSettingFleetController;
use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'.
Route::get('/one-setting/fleet', [OneSettingFleetController::class, 'index'])->name('one-setting.fleet.index');
