<?php

use App\Http\Middleware\EnsureCanonicalAppUrl;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SetLocale;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);

        $middleware->web(prepend: [
            EnsureCanonicalAppUrl::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($user === null) {
                return route('home');
            }

            return app(PostLoginRedirectService::class)->redirectPath($user);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
