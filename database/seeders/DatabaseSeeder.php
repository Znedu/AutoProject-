<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ServiceSeeder::class,
            BookingStatusSeeder::class,
            JobStatusSeeder::class,
            TicketStatusSeeder::class,
            AdminSeeder::class,
            BusinessSettingsSeeder::class,
        ]);

        $roles = Role::query()->pluck('id', 'slug');

        $users = [
            [
                'name' => 'Carlos Customer',
                'email' => 'customer@gmail.com',
                'phone' => '+63 915 222 3333',
                'role_id' => $roles[RoleSlug::Customer->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'Sarah Staff',
                'email' => 'staff1@gmail.com',
                'phone' => '+63 923 333 4444',
                'role_id' => $roles[RoleSlug::Staff->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],    
            [
                'name' => 'Mica Staff',
                'email' => 'staff2@gmail.com',
                'phone' => '+63 923 333 6666',
                'role_id' => $roles[RoleSlug::Staff->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'John Mechanic',
                'email' => 'mechanic1@gmail.com',
                'phone' => '+63 920 111 2222',
                'role_id' => $roles[RoleSlug::Mechanic->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'Mike Mechanic',
                'email' => 'mechanic2@gmail.com',
                'phone' => '+63 921 222 3333',
                'role_id' => $roles[RoleSlug::Mechanic->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
            [
                'name' => 'Alex Mechanic',
                'email' => 'mechanic3@gmail.com',
                'phone' => '+63 922 333 4444',
                'role_id' => $roles[RoleSlug::Mechanic->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
                        [
                'name' => 'Paul Mechanic',
                'email' => 'mechanic4@gmail.com',
                'phone' => '+63 923 333 4464',
                'role_id' => $roles[RoleSlug::Mechanic->value],
                'status' => User::STATUS_ACTIVE,
                'password' => 'demo123',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData,
            );
        }
    }
}
