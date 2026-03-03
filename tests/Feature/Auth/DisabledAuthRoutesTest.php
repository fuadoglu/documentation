<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisabledAuthRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_password_reset_routes_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/reset-password/any-token')->assertNotFound();
    }
}
