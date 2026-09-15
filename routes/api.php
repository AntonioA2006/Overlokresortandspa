<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal JSON endpoints
|--------------------------------------------------------------------------
| Session-authenticated JSON endpoints live in routes/web.php under the
| `/api` prefix so they share CSRF and the web session with Blade pages.
*/

Route::middleware('auth')->group(function () {
    // Intentionally empty: see routes/web.php for /api/* endpoints.
});
