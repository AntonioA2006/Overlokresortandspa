<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request, PostLoginRedirectService $postLoginRedirectService): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($postLoginRedirectService->redirectPath($user));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', __('auth.verification_sent'));
    }
}
