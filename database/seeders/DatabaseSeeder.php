<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

        // Seed Demo Users
        $users = [
            [
                'name' => 'Carlos Customer',
                'email' => 'customer@gmail.com',
                'phone' => '+63 915 222 3333',
                'role' => 'customer',
                'password' => bcrypt('demo123'),
            ],
            [
                'name' => 'Sarah Staff',
                'email' => 'staff@gmail.com',
                'phone' => '+63 923 333 4444',
                'role' => 'staff',
                'password' => bcrypt('demo123'),
            ],
            [
                'name' => 'John Mechanic',
                'email' => 'mechanic@gmail.com',
                'phone' => '+63 920 111 2222',
                'role' => 'mechanic',
                'password' => bcrypt('demo123'),
            ],
            [
                'name' => 'Chief Admin',
                'email' => 'admin@gmail.com',
                'phone' => '+63 912 345 6789',
                'role' => 'admin',
                'password' => bcrypt('demo123'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
