<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerNotePolicy
{
    public function viewAny(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function create(User $user, Customer $customer): bool|Response
    {
        return $user->isAdmin() || $customer->owner_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }

    public function delete(User $user, CustomerNote $note): bool|Response
    {
        return $user->isAdmin()
            || $note->customer?->owner_id === $user->id
            || $note->user_id === $user->id
            ? true
            : Response::denyAsNotFound();
    }
}
