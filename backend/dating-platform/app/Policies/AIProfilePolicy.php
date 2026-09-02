<?php

declare(strict_types=1);

namespace App\Policies;

use App\AIProfile;
use App\User;

final class AIProfilePolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function view(User $user, AIProfile $profile): bool
    {
        return $this->manage($user);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, AIProfile $profile): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, AIProfile $profile): bool
    {
        return $this->manage($user);
    }
}
