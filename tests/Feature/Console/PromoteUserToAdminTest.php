<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteUserToAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_promotes_an_existing_user_after_confirmation(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:promote-admin', ['email' => strtoupper($user->email)])
            ->expectsConfirmation("Promote {$user->email} to administrator?", 'yes')
            ->expectsOutputToContain("{$user->email} is now an administrator.")
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_command_supports_confirmed_non_interactive_provisioning(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:promote-admin', [
            'email' => $user->email,
            '--force' => true,
        ])
            ->expectsOutputToContain("{$user->email} is now an administrator.")
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_declining_confirmation_leaves_the_user_unchanged(): void
    {
        $user = User::factory()->create();

        $this->artisan('users:promote-admin', ['email' => $user->email])
            ->expectsConfirmation("Promote {$user->email} to administrator?", 'no')
            ->expectsOutputToContain('No changes were made.')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function test_command_fails_when_the_user_does_not_exist(): void
    {
        $this->artisan('users:promote-admin', ['email' => 'missing@example.com'])
            ->expectsOutputToContain('No user found with email [missing@example.com].')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_command_is_idempotent_for_an_existing_admin(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin'])->save();

        $this->artisan('users:promote-admin', ['email' => $user->email])
            ->expectsOutputToContain("{$user->email} already has administrator access.")
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }
}
