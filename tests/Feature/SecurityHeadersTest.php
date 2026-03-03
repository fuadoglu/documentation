<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_guest_pages(): void
    {
        $response = $this->get(route('login'));
        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeader('Content-Security-Policy');

        $this->assertStringNotContainsString("'unsafe-inline'", $contentSecurityPolicy);
    }
}
