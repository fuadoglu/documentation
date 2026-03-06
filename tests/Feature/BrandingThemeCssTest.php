<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingThemeCssTest extends TestCase
{
    use RefreshDatabase;

    public function test_branding_theme_css_route_returns_stylesheet(): void
    {
        $response = $this->get(route('branding.theme'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8');

        $response->assertSee('--color-brand:', false);
        $response->assertSee('--color-brand-rgb:', false);
        $response->assertSee('--color-brand-dark:', false);
    }
}

