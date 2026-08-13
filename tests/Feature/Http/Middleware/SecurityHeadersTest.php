<?php

namespace Tests\Feature\Http\Middleware;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function testBaselineHeadersArePresent(): void
    {
        $response = $this->get(route('pages.show', ['page' => 'about']));

        $response->assertOk();
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('X-XSS-Protection', '0');
    }
}
