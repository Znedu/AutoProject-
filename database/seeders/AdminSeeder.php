<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleId = Role::query()->where('slug', RoleSlug::Administrator->value)->value('id');

        if (!$adminRoleId) {
            $this->command->error('Administrator role not found. Please run RoleSeeder first.');
            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Chief Admin',
                'phone' => '+63 912 345 6789',
                'role_id' => $adminRoleId,
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ]
        );

        $this->command->info('Administrator account (admin@gmail.com) successfully seeded.');
    }
}
