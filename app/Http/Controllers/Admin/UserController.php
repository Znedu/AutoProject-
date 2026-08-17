<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('assignedRole')
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone ?? 'N/A',
                    'role'      => $user->assignedRole?->name ?? 'User',
                    'role_slug' => $user->assignedRole?->slug ?? 'customer',
                    'status'    => ucfirst($user->status),
                    'status_raw'=> $user->status,
                    'joinDate'  => $user->created_at ? $user->created_at->format('M d, Y') : '',
                ];
            });

        $roles = Role::select('id', 'name', 'slug')->get();

        return view('admin.users', [
            'users'         => $users,
            'roles'         => $roles,
            'currentUserId' => Auth::id(),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $role = Role::where('slug', $request->role)->firstOrFail();

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'role_id'           => $role->id,
            'status'            => $request->status,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user'    => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone ?? 'N/A',
                    'role'      => $role->name,
                    'role_slug' => $role->slug,
                    'status'    => ucfirst($user->status),
                    'status_raw'=> $user->status,
                    'joinDate'  => $user->created_at ? $user->created_at->format('M d, Y') : now()->format('M d, Y'),
                ],
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $user->name . ' created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $role = Role::where('slug', $request->role)->firstOrFail();

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'role_id' => $role->id,
            'status'  => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->refresh()->load('assignedRole');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user'    => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone ?? 'N/A',
                    'role'      => $user->assignedRole?->name ?? 'User',
                    'role_slug' => $user->assignedRole?->slug ?? 'customer',
                    'status'    => ucfirst($user->status),
                    'status_raw'=> $user->status,
                    'joinDate'  => $user->created_at ? $user->created_at->format('M d, Y') : '',
                ],
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $user->name . ' updated successfully.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        // Prevent logged-in admin from deleting their own account
        if ($user->id === Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own admin account.',
                ], 403);
            }

            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own admin account.');
        }

        $userName = $user->name;
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User ' . $userName . ' deleted successfully.',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $userName . ' deleted successfully.');
    }
}
