<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\ConversationController as ApiConversationController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\NotificationController;
use App\Http\Controllers\Guest\ReservationController;
use App\Http\Controllers\Guest\RoomCatalogController;
use App\Http\Controllers\Guest\SupportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Reception\CheckInController;
use App\Http\Controllers\Reception\DashboardController as ReceptionDashboardController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::post('/logout', [LogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('/rooms', [RoomCatalogController::class, 'index'])->name('rooms.index');

    Route::get('/reservations/search', [ReservationController::class, 'search'])->name('reservations.search');
    Route::get('/reservations/results', [ReservationController::class, 'results'])->name('reservations.results');
    Route::get('/reservations/rooms/{roomType:slug}', [ReservationController::class, 'showRoom'])->name('reservations.rooms.show');

    Route::middleware('auth')->group(function () {
        Route::get('/reservations/checkout/{room}', [ReservationController::class, 'checkout'])->name('reservations.checkout');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::post('/support/{conversation}/messages', [SupportController::class, 'store'])->name('support.messages');
    });
});

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/notifications', [ApiNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [ApiNotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/conversations', [ApiConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}/messages', [ApiConversationController::class, 'messages'])->name('conversations.messages');
    Route::post('/conversations/{conversation}/messages', [ApiConversationController::class, 'storeMessage'])->name('conversations.messages.store');
});

Route::middleware(['auth', 'role:reception,admin'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('/', [ReceptionDashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan', [CheckInController::class, 'scan'])->name('scan');
    Route::get('/check/{token}', [CheckInController::class, 'show'])->name('check');
    Route::post('/check/{token}/verify', [CheckInController::class, 'verify'])->name('check.verify');
    Route::post('/check/{token}/complete', [CheckInController::class, 'complete'])->name('check.complete');
    Route::post('/check/{token}/checkout', [CheckInController::class, 'checkout'])->name('check.checkout');
});

Route::middleware(['auth', 'role:support,admin'])->prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportDashboardController::class, 'index'])->name('dashboard');
    Route::get('/conversations/{conversation}', [SupportDashboardController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{conversation}/messages', [SupportDashboardController::class, 'reply'])->name('conversations.reply');
    Route::post('/conversations/{conversation}/close', [SupportDashboardController::class, 'close'])->name('conversations.close');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});
