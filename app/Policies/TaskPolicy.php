<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool|Response
    {
        return $user->isAdmin()
            || $task->customer?->owner_id === $user->id
            || $task->assigned_to === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function create(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function update(User $user, Task $task): bool|Response
    {
        return $user->isAdmin()
            || $task->customer?->owner_id === $user->id
            || $task->assigned_to === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Task $task): bool|Response
    {
        return $user->isAdmin()
            || $task->created_by === $user->id
            || $task->customer?->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }
}
