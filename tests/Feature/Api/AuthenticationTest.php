<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_member_and_returns_a_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Irakli Mazmishvili',
            'email' => 'irakli@example.com',
            'password' => 'strong-password-123',
            'password_confirmation' => 'strong-password-123',
            'role' => 'admin',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'irakli@example.com')
            ->assertJsonPath('data.role', 'member')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'role', 'created_at'],
                'token',
                'token_type',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'irakli@example.com',
            'role' => 'member',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_registration_validates_email_uniqueness_and_password_confirmation(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'strong-password-123',
            'password_confirmation' => 'different-password-123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'strong-password-123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'member@example.com',
            'password' => 'strong-password-123',
        ])
            ->assertOk()
            ->assertJsonPath('data.email', 'member@example.com')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role'], 'token']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'strong-password-123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'member@example.com',
            'password' => 'incorrect-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_retrieve_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-device')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', 'member');
    }

    public function test_guest_cannot_retrieve_the_authenticated_profile(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_logout_revokes_only_the_current_access_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current-device');
        $otherToken = $user->createToken('other-device');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
    }
}
