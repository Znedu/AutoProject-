<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The destination email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('email');

        $this->info("Sending test email to: {$recipient}...");

        try {
            Mail::raw("Hello! This is a test email from AutoProject+ to verify your SMTP mail configuration.", function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('AutoProject+ SMTP Test Email');
            });

            $this->info("Test email sent successfully to {$recipient}!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to send test email: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
