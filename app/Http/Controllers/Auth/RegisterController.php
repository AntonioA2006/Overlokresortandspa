<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Auth\PostLoginRedirectService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'hotelName' => config('overlook.hotel_name'),
        ]);
    }

    public function store(
        RegisterRequest $request,
        PostLoginRedirectService $postLoginRedirectService,
    ): RedirectResponse {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => UserRole::Guest,
            'auth_provider' => null,
            'auth_provider_id' => null,
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(
            $postLoginRedirectService->redirectPath($user),
        );
    }
}
