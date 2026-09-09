<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleSlug;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Allow unverified customer to access register form to change/correct email
            if (! ($user->isCustomer() && ! $user->hasVerifiedEmail())) {
                return redirect(DashboardRedirectService::pathFor($user));
            }
        }

        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $existingUser = User::withTrashed()->where('email', $request->email)->first();

        if ($existingUser) {
            if ($existingUser->trashed()) {
                // If previously deleted by admin, restore user as an active customer requiring verification
                $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

                $existingUser->restore();
                $existingUser->update([
                    'name' => $request->name,
                    'password' => $request->password,
                    'role_id' => $customerRole->id,
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => null,
                ]);

                $user = $existingUser;
            } elseif ($existingUser->hasVerifiedEmail()) {
                return back()->withErrors([
                    'email' => 'This email address is already registered and verified. Please log in instead.',
                ])->withInput();
            } else {
                // Unverified user: update pending account details & password
                $existingUser->update([
                    'name' => $request->name,
                    'password' => $request->password,
                    'status' => User::STATUS_ACTIVE,
                ]);

                $user = $existingUser;
            }
        } else {
            $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $customerRole->id,
                'status' => User::STATUS_ACTIVE,
                'password' => $request->password,
            ]);
        }

        Auth::logout();
        session(['verification_email' => $user->email]);

        try {
            app(EmailVerificationService::class)->sendCode($user);
        } catch (\Throwable $e) {
            \Log::error('[Register] sendCode failed unexpectedly', [
                'user_id' => $user->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('verification.notice')
            ->with('success', 'A 6-digit verification code has been sent to your email address.');
    }
}
