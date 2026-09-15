<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\RoomTypeController as AdminRoomTypeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\ConversationController as ApiConversationController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Guest\HomeController;
use App\Http\Controllers\Guest\NotificationController;
use App\Http\Controllers\Guest\ReservationController;
use App\Http\Controllers\Guest\RoomCatalogController;
use App\Http\Controllers\Guest\SupportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-email')
        ->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::post('/logout', [LogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

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
    Route::post('/lookup', [CheckInController::class, 'lookup'])->name('lookup');
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

    Route::get('/rooms', [AdminRoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [AdminRoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [AdminRoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [AdminRoomController::class, 'edit'])->name('rooms.edit');
    Route::post('/rooms/{room}', [AdminRoomController::class, 'update'])->name('rooms.update');
    Route::post('/rooms/{room}/status', [AdminRoomController::class, 'updateStatus'])->name('rooms.status');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::get('/room-types', [AdminRoomTypeController::class, 'index'])->name('room-types.index');
    Route::get('/room-types/{roomType}/edit', [AdminRoomTypeController::class, 'edit'])->name('room-types.edit');
    Route::post('/room-types/{roomType}', [AdminRoomTypeController::class, 'update'])->name('room-types.update');
});
