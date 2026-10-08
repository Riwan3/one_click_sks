<?php

use Illuminate\Support\Facades\Route;

// Dimuat dari routes/web.php di dalam group middleware 'auth'. File lain di folder ini ditambah per fitur.
// Pintu masuk modul: yang boleh melihat roster ke Dashboard, selain itu (mis. operator) langsung ke Share Roster.
Route::get('/one-roster', fn () => redirect()->route(
    auth()->user()->canViewRosterV2() ? 'one-roster.dashboard' : 'one-roster.share'
))->name('one-roster.index');
