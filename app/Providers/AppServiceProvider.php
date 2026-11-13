<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\Task;
use App\Policies\CustomerNotePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CustomerNote::class, CustomerNotePolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
