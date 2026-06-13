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
            return redirect(DashboardRedirectService::pathFor(Auth::user()));
        }

        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => $customerRole->id,
            'status' => User::STATUS_ACTIVE,
            'password' => $request->password,
        ]);

        Auth::login($user);

        return redirect(DashboardRedirectService::pathFor($user))
            ->with('success', 'Account created successfully!');
    }
}
