<?php

namespace App\Policies;

use App\Models\InboundLead;
use App\Models\User;

class InboundLeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, InboundLead $lead): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, InboundLead $lead): bool
    {
        return $user->isAdmin();
    }
}
