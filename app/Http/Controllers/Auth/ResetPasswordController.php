<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'hotelName' => config('overlook.hotel_name'),
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(
        ResetPasswordRequest $request,
        PostLoginRedirectService $postLoginRedirectService,
    ): RedirectResponse {
        $status = Password::reset(
            $request->safe()->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended($postLoginRedirectService->redirectPath($user))
            ->with('status', __($status));
    }
}
