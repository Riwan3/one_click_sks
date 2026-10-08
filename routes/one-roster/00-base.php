<?php

use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. File lain di folder ini ditambah per fitur.
Route::get('/one-roster', fn () => redirect()->route('one-roster.dashboard'))->name('one-roster.index');
