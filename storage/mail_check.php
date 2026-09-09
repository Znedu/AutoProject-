<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\Auth\EmailVerificationService;
use App\Models\EmailVerificationCode;

// Use the most recently registered user
$user = User::latest()->first();

echo "Testing with user: {$user->name} ({$user->email})\n";
echo "User ID: {$user->id}\n\n";

// Step 1: Check if EmailVerificationCode model works
echo "--- Step 1: Check DB table ---\n";
$countBefore = EmailVerificationCode::where('user_id', $user->id)->count();
echo "Existing codes for user: {$countBefore}\n\n";

// Step 2: Try sending the code exactly as RegisterController does
echo "--- Step 2: Calling sendCode() ---\n";
try {
    $service = app(EmailVerificationService::class);
    $service->sendCode($user);
    echo "sendCode() completed without exception!\n";
} catch (\Throwable $e) {
    echo "sendCode() FAILED:\n";
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Step 3: Check if the code was stored in DB
echo "\n--- Step 3: Check DB after send ---\n";
$codes = EmailVerificationCode::where('user_id', $user->id)->get();
echo "Codes in DB after send: " . $codes->count() . "\n";
foreach ($codes as $code) {
    echo "  - ID: {$code->id}, Created: {$code->created_at}, Expires: {$code->expires_at}, Attempts: {$code->attempts}\n";
}

echo "\nDone!\n";
