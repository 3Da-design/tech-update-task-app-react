<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * S2: 新規登録は React SPA が /api/register を JSON で呼ぶ方式（Blade /register は廃止）。
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register_via_api(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.email', 'newuser@example.com');
        $this->assertAuthenticated();
    }

    public function test_registration_requires_confirmed_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422);
        $this->assertGuest();
    }
}
