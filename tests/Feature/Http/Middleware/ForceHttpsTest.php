<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForceHttpsTest extends TestCase
{
    private const PROBE_PATH = '/__middleware-probe';

    private string $productionUrl;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get(self::PROBE_PATH, fn() => 'ok');
        Route::middleware('web')->post(self::PROBE_PATH, fn() => 'ok');

        $this->productionUrl = config('app.production_url');
    }

    public function testPlaintextRequestIsRedirectedToHttpsInProduction(): void
    {
        $this->runInProduction();

        $response = $this->get($this->plaintextProductionUrl());

        $response->assertStatus(308);
        $response->assertRedirect($this->productionUrl . self::PROBE_PATH);
    }

    public function testRedirectPreservesNonGetRequests(): void
    {
        $this->runInProduction();

        $response = $this->post($this->plaintextProductionUrl());

        // 308, unlike 301, forbids the client from downgrading POST to GET.
        $response->assertStatus(308);
    }

    public function testRedirectCarriesTheBaselineSecurityHeaders(): void
    {
        $this->runInProduction();

        $response = $this->get($this->plaintextProductionUrl());

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('X-XSS-Protection', '0');
    }

    public function testSecureRequestIsNotRedirectedInProduction(): void
    {
        $this->runInProduction();

        $response = $this->get($this->productionUrl . self::PROBE_PATH);

        $response->assertOk();
    }

    public function testPlaintextRequestIsNotRedirectedOutsideProduction(): void
    {
        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
    }

    private function runInProduction(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
    }

    /**
     * The production host, so that RedirectIfProduction — which runs earlier in
     * the global stack — lets the request through to ForceHttps.
     */
    private function plaintextProductionUrl(): string
    {
        return str_replace('https://', 'http://', $this->productionUrl) . self::PROBE_PATH;
    }
}
