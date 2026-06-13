<?php

namespace App\Services\Auth;

use App\Enums\RoleSlug;
use App\Models\User;

class DashboardRedirectService
{
    public static function pathFor(User $user): string
    {
        $slug = $user->roleSlug();

        return RoleSlug::tryFrom($slug)?->dashboardPath() ?? RoleSlug::Customer->dashboardPath();
    }

    public static function routeNameFor(User $user): string
    {
        return match (RoleSlug::tryFrom($user->roleSlug())) {
            RoleSlug::Administrator => 'admin.dashboard',
            RoleSlug::Staff => 'staff.dashboard',
            RoleSlug::Mechanic => 'mechanic.dashboard',
            default => 'customer.dashboard',
        };
    }
}
