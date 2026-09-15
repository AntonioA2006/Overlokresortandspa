<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(
        EmailVerificationRequest $request,
        PostLoginRedirectService $postLoginRedirectService,
    ): RedirectResponse {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($postLoginRedirectService->redirectPath($user));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()
            ->intended($postLoginRedirectService->redirectPath($user))
            ->with('status', __('auth.verified'));
    }
}
