<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\ReservationController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Reception\DashboardController as ReceptionDashboardController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::post('/logout', [LogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('guest')->name('guest.')->group(function () {
    Route::view('/rooms', 'guest.rooms.index')->name('rooms.index');

    Route::get('/reservations/search', [ReservationController::class, 'search'])->name('reservations.search');
    Route::get('/reservations/results', [ReservationController::class, 'results'])->name('reservations.results');
    Route::get('/reservations/rooms/{roomType:slug}', [ReservationController::class, 'showRoom'])->name('reservations.rooms.show');

    Route::middleware('auth')->group(function () {
        Route::get('/reservations/checkout/{room}', [ReservationController::class, 'checkout'])->name('reservations.checkout');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::view('/notifications', 'guest.notifications.index')->name('notifications.index');
        Route::view('/support', 'guest.support.index')->name('support.index');
    });
});

Route::middleware(['auth', 'role:reception,admin'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('/', [ReceptionDashboardController::class, 'index'])->name('dashboard');
    Route::view('/scan', 'reception.scan')->name('scan');
    Route::view('/check/{token}', 'reception.check')->name('check');
});

Route::middleware(['auth', 'role:support,admin'])->prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});
