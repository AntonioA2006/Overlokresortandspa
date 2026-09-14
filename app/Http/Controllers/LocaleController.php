<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * @var list<string>
     */
    private const SUPPORTED_LOCALES = ['es', 'en'];

    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        $redirect = $request->query('redirect');

        if (is_string($redirect) && $this->isSafeRedirect($redirect)) {
            return redirect($redirect);
        }

        return redirect()->back();
    }

    private function isSafeRedirect(string $url): bool
    {
        return str_starts_with($url, url('/'));
    }
}
