<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleOAuthService;
use App\Services\Auth\PostLoginRedirectService;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return $this->googleProvider()
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
            $googleUser = $this->googleProvider()->user();
            $user = $googleOAuthService->resolveAuthenticatedUser($googleUser);

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended($postLoginRedirectService->redirectPath($user));
        } catch (InvalidStateException $exception) {
            Log::warning('Google OAuth invalid state.', [
                'message' => $exception->getMessage(),
                'request_host' => request()->getHost(),
                'app_url' => config('app.url'),
                'redirect_uri' => config('services.google.redirect'),
                'session_id' => request()->hasSession() ? request()->session()->getId() : null,
                'state_param_present' => request()->filled('state'),
            ]);

            return redirect()
                ->route('login')
                ->with('auth_error', 'La sesión de autenticación expiró. Intenta nuevamente.');
        } catch (Throwable $exception) {
            Log::error('Google OAuth callback failed.', [
                'message' => $exception->getMessage(),
                'request_host' => request()->getHost(),
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('login')
                ->with('auth_error', 'No pudimos completar el inicio de sesión con Google. Intenta más tarde.');
        }
    }

    private function googleProvider(): Provider
    {
        $provider = Socialite::driver('google');

        if (! app()->environment('local')) {
            return $provider;
        }

        $caBundle = $this->resolveCaBundlePath();

        if ($caBundle !== null) {
            $provider->setHttpClient(new Client([
                'verify' => $caBundle,
            ]));
        }

        return $provider;
    }

    private function resolveCaBundlePath(): ?string
    {
        $configured = env('SSL_CA_BUNDLE');

        if (is_string($configured) && $configured !== '' && file_exists($configured)) {
            return $configured;
        }

        $projectBundle = storage_path('app/cacert.pem');

        return file_exists($projectBundle) ? $projectBundle : null;
    }

    private function friendlyOAuthErrorMessage(string $error): string
    {
        return match ($error) {
            'access_denied' => 'Cancelaste el inicio de sesión con Google.',
            'invalid_request' => 'La solicitud de autenticación no es válida.',
            'redirect_uri_mismatch' => 'La URI de redirección no coincide con la configurada en Google Cloud Console.',
            default => 'No pudimos completar el inicio de sesión con Google.',
        };
    }
}
