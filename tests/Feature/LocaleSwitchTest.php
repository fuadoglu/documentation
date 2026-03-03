<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_locale_and_store_it_in_session(): void
    {
        $response = $this->from('/login')->post(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('locale', 'en');

        $this->withSession(['locale' => 'en'])
            ->get('/login')
            ->assertSee('Use your company email to sign in.');
    }

    public function test_authenticated_user_locale_is_persisted_to_database(): void
    {
        $user = User::factory()->create([
            'locale' => 'az',
        ]);

        $response = $this->actingAs($user)
            ->from('/dashboard')
            ->post(route('locale.update'), [
                'locale' => 'en',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('locale', 'en');

        $this->assertSame('en', $user->fresh()->locale);
    }
}
