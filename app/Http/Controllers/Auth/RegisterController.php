<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleSlug;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\DashboardRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $existingUser = User::query()->where('email', $request->email)->first();

        if ($existingUser) {
            if ($existingUser->hasVerifiedEmail()) {
                return back()->withErrors([
                    'email' => 'This email address is already registered and verified. Please log in instead.',
                ])->withInput();
            }

            // Unverified user: update pending account details & password
            $existingUser->update([
                'name'     => $request->name,
                'password' => $request->password,
                'status'   => User::STATUS_ACTIVE,
            ]);

            $user = $existingUser;
        } else {
            $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'role_id'  => $customerRole->id,
                'status'   => User::STATUS_ACTIVE,
                'password' => $request->password,
            ]);
        }

        Auth::logout();
        session(['verification_email' => $user->email]);

        app(\App\Services\Auth\EmailVerificationService::class)->sendCode($user);

        return redirect()->route('verification.notice')
            ->with('success', 'A 6-digit verification code has been sent to your email address.');
    }
}
