<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserToAdmin extends Command
{
    protected $signature = 'users:promote-admin
                            {email : Email address of an existing user}
                            {--force : Skip the interactive confirmation}';

    protected $description = 'Promote an existing user to the administrator role';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->components->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->components->info("{$user->email} already has administrator access.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Promote {$user->email} to administrator?")) {
            $this->components->warn('No changes were made.');

            return self::FAILURE;
        }

        $user->forceFill(['role' => 'admin'])->save();

        $this->components->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
