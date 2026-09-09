<?php

namespace App\Services\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\Auth\EmailVerificationCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    /**
     * Resend cooldown duration in seconds.
     */
    public const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Code expiry duration in minutes.
     */
    public const EXPIRY_MINUTES = 15;

    /**
     * Maximum failed verification attempts before invalidating the code.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * Generate, store, and send a new 6-digit verification code to the user.
     */
    public function sendCode(User $user): void
    {
        $this->invalidateAllCodes($user);

        $plainCode = (string) random_int(100000, 999999);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        try {
            \Log::info('[EmailVerification] Sending notification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
            ]);

            $user->notify(new EmailVerificationCodeNotification($plainCode));

            \Log::info('[EmailVerification] Notification sent successfully', ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            \Log::error('[EmailVerification] Notification failed, trying direct Mail::send fallback', [
                'user_id' => $user->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            // Fallback: send using Mail facade directly
            try {
                Mail::send('mail.auth.verify-code-plain', [
                    'name' => $user->name,
                    'code' => $plainCode,
                ], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Verify your AutoProject+ account');
                });

                \Log::info('[EmailVerification] Fallback Mail::send succeeded', ['user_id' => $user->id]);
            } catch (\Throwable $fallbackException) {
                \Log::error('[EmailVerification] Fallback Mail::send also failed', [
                    'user_id' => $user->id,
                    'exception' => get_class($fallbackException),
                    'message' => $fallbackException->getMessage(),
                    'trace' => $fallbackException->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Verify the 6-digit code for a user.
     *
     * @return array{success: bool, message: string}
     */
    public function verify(User $user, string $code): array
    {
        try {
            $record = EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if (! $record) {
                return [
                    'success' => false,
                    'message' => 'No verification code found. Please request a new code.',
                ];
            }

            if ($record->isExpired()) {
                $record->delete();

                return [
                    'success' => false,
                    'message' => 'Verification code has expired. Please request a new code.',
                ];
            }

            if ($record->attempts >= self::MAX_ATTEMPTS) {
                $record->delete();

                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. This code is no longer valid. Please request a new code.',
                ];
            }

            if (! Hash::check($code, $record->code)) {
                $record->increment('attempts');

                if ($record->attempts >= self::MAX_ATTEMPTS) {
                    $record->delete();

                    return [
                        'success' => false,
                        'message' => 'Too many failed attempts (5/5). Please request a new verification code.',
                    ];
                }

                $remaining = self::MAX_ATTEMPTS - $record->attempts;

                return [
                    'success' => false,
                    'message' => "Invalid verification code. {$remaining} attempts remaining.",
                ];
            }

            // Code is correct
            $user->markEmailAsVerified();
            $this->invalidateAllCodes($user);

            return [
                'success' => true,
                'message' => 'Email verified successfully!',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'An error occurred during verification. Please try requesting a new code.',
            ];
        }
    }

    /**
     * Check if a user is allowed to request a resend (60s cooldown).
     */
    public function canResend(User $user): bool
    {
        try {
            $latestCode = EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if (! $latestCode) {
                return true;
            }

            return $latestCode->created_at->diffInSeconds(now()) >= self::RESEND_COOLDOWN_SECONDS;
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Get remaining cooldown seconds before user can resend.
     */
    public function getCooldownSecondsRemaining(User $user): int
    {
        try {
            $latestCode = EmailVerificationCode::query()
                ->where('user_id', $user->id)
                ->latest()
                ->first();

            if (! $latestCode) {
                return 0;
            }

            $elapsed = $latestCode->created_at->diffInSeconds(now());
            $remaining = self::RESEND_COOLDOWN_SECONDS - $elapsed;

            return max(0, (int) $remaining);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Delete all codes for the given user.
     */
    public function invalidateAllCodes(User $user): void
    {
        try {
            EmailVerificationCode::query()->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // Ignore if already clean
        }
    }
}
