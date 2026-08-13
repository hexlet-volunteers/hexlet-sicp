<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    private const PROBE_PATH = '/__middleware-probe';

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get(self::PROBE_PATH, fn() => 'ok');
    }

    public function testBaselineHeadersArePresent(): void
    {
        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('X-XSS-Protection', '0');
    }

    public function testStrictTransportSecurityIsSentOverHttps(): void
    {
        $response = $this->get('https://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }

    public function testStrictTransportSecurityIsAbsentOverPlainHttp(): void
    {
        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function testSessionCookieIsSecureWhenConfigured(): void
    {
        config(['session.secure' => true]);

        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $this->assertTrue($this->sessionCookie($response->baseResponse->headers->getCookies())->isSecure());
    }

    public function testSessionCookieIsNotSecureOutsideProduction(): void
    {
        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $this->assertFalse(config('session.secure'));
        $this->assertFalse($this->sessionCookie($response->baseResponse->headers->getCookies())->isSecure());
    }

    public function testSessionCookieUsesLaxSameSite(): void
    {
        $response = $this->get('http://localhost' . self::PROBE_PATH);

        $response->assertOk();
        $this->assertSame(
            Cookie::SAMESITE_LAX,
            $this->sessionCookie($response->baseResponse->headers->getCookies())->getSameSite(),
        );
    }

    /**
     * @param  array<int, Cookie>  $cookies
     */
    private function sessionCookie(array $cookies): Cookie
    {
        $name = config('session.cookie');

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        $this->fail("Session cookie [{$name}] was not set on the response.");
    }
}
