<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleOAuthService;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(
        GoogleOAuthService $googleOAuthService,
        PostLoginRedirectService $postLoginRedirectService,
    ): RedirectResponse {
        if (request()->filled('error')) {
            return redirect()
                ->route('login')
                ->with('auth_error', $this->friendlyOAuthErrorMessage((string) request('error')));
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $googleOAuthService->resolveAuthenticatedUser($googleUser);

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended($postLoginRedirectService->redirectPath($user));
        } catch (InvalidStateException $exception) {
            Log::warning('Google OAuth invalid state.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->with('auth_error', 'La sesión de autenticación expiró. Intenta nuevamente.');
        } catch (Throwable $exception) {
            Log::error('Google OAuth callback failed.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->with('auth_error', 'No pudimos completar el inicio de sesión con Google. Intenta más tarde.');
        }
    }

    private function friendlyOAuthErrorMessage(string $error): string
    {
        return match ($error) {
            'access_denied' => 'Cancelaste el inicio de sesión con Google.',
            'invalid_request' => 'La solicitud de autenticación no es válida.',
            default => 'No pudimos completar el inicio de sesión con Google.',
        };
    }
}
