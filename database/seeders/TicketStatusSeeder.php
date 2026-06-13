<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Support ticket statuses are managed via code constants/enums in the SupportTicket model and stored directly in a string column. No database entries are required.');
    }
}
