<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    public function test_profile_can_be_updated_with_allowed_domain(): void
    {
        $user = User::factory()->create([
            'email' => 'old@company.az',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Yeni Ad',
                'email' => 'new@company.az',
                'locale' => 'en',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Yeni Ad', $user->name);
        $this->assertSame('new@company.az', $user->email);
        $this->assertSame('en', $user->locale);
    }

    public function test_profile_update_rejects_unallowed_domain(): void
    {
        $user = User::factory()->create([
            'email' => 'old@company.az',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Yeni Ad',
                'email' => 'new@example.com',
                'locale' => 'az',
            ]);

        $response->assertSessionHasErrors('email');
    }
}
