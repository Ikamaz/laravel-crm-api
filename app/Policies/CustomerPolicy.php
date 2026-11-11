<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function delete(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }
}
