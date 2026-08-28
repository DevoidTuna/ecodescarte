<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the team login: issuing the token, rejecting invalid credentials,
 * and the fact that the raw token is never persisted.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function teamMember(): User
    {
        return User::factory()->create([
            'username' => 'equipe',
            'password' => 'secret',
        ]);
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        $user = $this->teamMember();

        $this->postJson('/api/login', [
            'username' => 'equipe',
            'password' => 'secret',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['username', 'name']])
            ->assertJsonPath('user.username', 'equipe')
            ->assertJsonPath('user.name', $user->name);
    }

    public function test_login_fails_with_the_wrong_password(): void
    {
        $this->teamMember();

        $this->postJson('/api/login', [
            'username' => 'equipe',
            'password' => 'senha-errada',
        ])->assertUnauthorized();
    }

    public function test_login_fails_for_an_unknown_username(): void
    {
        $this->postJson('/api/login', [
            'username' => 'ninguem',
            'password' => 'secret',
        ])->assertUnauthorized();
    }

    public function test_login_requires_username_and_password(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_only_the_hash_of_the_token_is_stored(): void
    {
        $user = $this->teamMember();

        $token = $this->postJson('/api/login', [
            'username' => 'equipe',
            'password' => 'secret',
        ])->json('token');

        $stored = $user->fresh()->api_token;

        // The value handed to the client must not be the one held in the database.
        $this->assertNotSame($token, $stored);
        $this->assertSame(hash('sha256', $token), $stored);
    }

    public function test_logout_invalidates_the_token(): void
    {
        $this->teamMember();

        $token = $this->postJson('/api/login', [
            'username' => 'equipe',
            'password' => 'secret',
        ])->json('token');

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)->getJson('/api/admin/points')->assertOk();

        $this->withHeaders($headers)->postJson('/api/logout')->assertNoContent();

        // The same token is no longer valid once logged out.
        $this->withHeaders($headers)->getJson('/api/admin/points')->assertUnauthorized();
    }
}
