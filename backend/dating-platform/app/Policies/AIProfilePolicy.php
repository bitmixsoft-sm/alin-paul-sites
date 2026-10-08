<?php

declare(strict_types=1);

namespace App\Policies;

use App\AIProfile;
use App\User;

final class AIProfilePolicy
{
    public function manage(User $user): bool
    {
        // Admin-only per the client's explicit request (2026-10-02) - deliberately NOT
        // isAdmin() (admin OR editor), which editors were slipping through on, unlike
        // most other admin-shared sections where that broader check is intended.
        return $user->role === 'admin';
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
