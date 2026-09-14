<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal JSON endpoints for dynamic UI (chat, notifications, etc.)
|--------------------------------------------------------------------------
| These routes will be implemented incrementally. Middleware auth:sanctum
| or auth:web + CSRF will be applied per endpoint when features are built.
*/

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // GET    /api/notifications
        // POST   /api/notifications/{notification}/read
    });

    Route::prefix('conversations')->name('conversations.')->group(function () {
        // GET    /api/conversations
        // GET    /api/conversations/{conversation}/messages
        // POST   /api/conversations/{conversation}/messages
    });

    Route::prefix('reservations')->name('reservations.')->group(function () {
        // GET    /api/reservations/{reservation}
        // POST   /api/reservations
        // POST   /api/reservations/{reservation}/verify
        // POST   /api/reservations/{reservation}/check-in
    });
});
