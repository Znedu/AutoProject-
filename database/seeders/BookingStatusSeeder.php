<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Booking statuses are managed via code constants/enums in the Booking model and stored directly in a string column. No database entries are required.');
    }
}
