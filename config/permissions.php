<?php

use App\Enums\Permission;
use App\Enums\RoleSlug;

return [

    /*
    |--------------------------------------------------------------------------
    | Role → Permission Map
    |--------------------------------------------------------------------------
    |
    | Canonical permission assignments for each role. Database seeders use this
    | config as the single source of truth. Administrators receive all permissions.
    |
    */

    'roles' => [
        RoleSlug::Customer->value => array_map(
            fn (Permission $permission) => $permission->value,
            Permission::forRole(RoleSlug::Customer),
        ),
        RoleSlug::Staff->value => array_map(
            fn (Permission $permission) => $permission->value,
            Permission::forRole(RoleSlug::Staff),
        ),
        RoleSlug::Mechanic->value => array_map(
            fn (Permission $permission) => $permission->value,
            Permission::forRole(RoleSlug::Mechanic),
        ),
        RoleSlug::Administrator->value => array_map(
            fn (Permission $permission) => $permission->value,
            Permission::forRole(RoleSlug::Administrator),
        ),
    ],

];
