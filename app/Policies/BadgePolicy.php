<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;

class BadgePolicy
{
    public function view(User $user, Badge $badge): bool
    {
        return $user->id === $badge->user_id;
    }
}
