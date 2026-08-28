<?php

use Illuminate\Support\Facades\Route;

// The Vue SPA is served from a single Blade view.
// Any route other than /api or /up falls through to the SPA, which handles the
// routing client-side (the map at "/" and the team area at "/admin").
Route::view('/{any?}', 'app')
    ->where('any', '^(?!api|up).*$');
