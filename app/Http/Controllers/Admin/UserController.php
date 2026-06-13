<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('assignedRole')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? 'N/A',
                    'role' => $user->assignedRole?->name ?? 'User',
                    'status' => ucfirst($user->status),
                    'joinDate' => $user->created_at ? $user->created_at->format('M d, Y') : '',
                ];
            });

        return view('admin.users', [
            'users' => $users,
        ]);
    }
}
