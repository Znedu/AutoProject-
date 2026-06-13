<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\RoleSlug;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles, permissions, and role-permission assignments.
     */
    public function run(): void
    {
        $roles = [
            RoleSlug::Customer->value => [
                'name' => RoleSlug::Customer->label(),
                'description' => 'Books services, tracks progress, and manages profile.',
            ],
            RoleSlug::Staff->value => [
                'name' => RoleSlug::Staff->label(),
                'description' => 'Manages booking queue and customer support.',
            ],
            RoleSlug::Mechanic->value => [
                'name' => RoleSlug::Mechanic->label(),
                'description' => 'Executes assigned jobs and posts service updates.',
            ],
            RoleSlug::Administrator->value => [
                'name' => RoleSlug::Administrator->label(),
                'description' => 'Full system administration and reporting access.',
            ],
        ];

        foreach ($roles as $slug => $attributes) {
            Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attributes['name'],
                    'description' => $attributes['description'],
                ],
            );
        }

        foreach (PermissionEnum::cases() as $permissionCase) {
            Permission::query()->updateOrCreate(
                ['slug' => $permissionCase->value],
                [
                    'name' => str($permissionCase->name)->headline()->replace('-', ' ')->toString(),
                    'group' => $permissionCase->group(),
                ],
            );
        }

        $permissionIds = Permission::query()->pluck('id', 'slug');

        foreach (config('permissions.roles', []) as $roleSlug => $permissionSlugs) {
            $role = Role::query()->where('slug', $roleSlug)->first();

            if ($role === null) {
                continue;
            }

            $ids = collect($permissionSlugs)
                ->map(fn (string $slug) => $permissionIds[$slug] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($ids);
        }
    }
}
