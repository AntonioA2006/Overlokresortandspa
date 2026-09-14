<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EnsureCanonicalAppUrl;
use Illuminate\Http\Request;
use Tests\TestCase;

class EnsureCanonicalAppUrlTest extends TestCase
{
    public function test_it_redirects_127_to_configured_app_url_host_in_local_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.url' => 'http://localhost:8000']);

        $middleware = new EnsureCanonicalAppUrl;
        $request = Request::create('/login', 'GET', [], [], [], [
            'HTTP_HOST' => '127.0.0.1:8000',
            'SERVER_PORT' => '8000',
        ]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame('http://localhost:8000/login', $response->headers->get('Location'));
    }

    public function test_it_does_not_redirect_when_host_matches_app_url(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.url' => 'http://localhost:8000']);

        $middleware = new EnsureCanonicalAppUrl;
        $request = Request::create('/login', 'GET', [], [], [], [
            'HTTP_HOST' => 'localhost:8000',
            'SERVER_PORT' => '8000',
        ]);

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }
}
