<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalAppUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local')) {
            return $next($request);
        }

        $appUrl = parse_url((string) config('app.url'));
        $canonicalHost = $appUrl['host'] ?? null;

        if ($canonicalHost === null || $request->getHost() === $canonicalHost) {
            return $next($request);
        }

        $scheme = $appUrl['scheme'] ?? $request->getScheme();
        $port = $appUrl['port'] ?? $request->getPort();
        $portSuffix = $port && ! in_array((int) $port, [80, 443], true) ? ':'.$port : '';

        return redirect()->away($scheme.'://'.$canonicalHost.$portSuffix.$request->getRequestUri());
    }
}
