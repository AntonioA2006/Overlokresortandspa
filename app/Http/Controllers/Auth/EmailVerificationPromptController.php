<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request, PostLoginRedirectService $postLoginRedirectService): RedirectResponse|View
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($postLoginRedirectService->redirectPath($user));
        }

        return view('auth.verify-email', [
            'hotelName' => config('overlook.hotel_name'),
            'user' => $user,
        ]);
    }
}
