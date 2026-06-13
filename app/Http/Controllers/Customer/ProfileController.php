<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $profile = $user->customerProfile;
        
        $address = '';
        if ($profile) {
            $parts = array_filter([$profile->address, $profile->city, $profile->province]);
            $address = implode(', ', $parts);
        }

        $profileData = [
            'fullName' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'address' => $address ?: 'Please set your address',
        ];

        // Stats
        $totalBookings = Booking::forUser($user->id)->count();
        $completedServices = Booking::forUser($user->id)->status(Booking::STATUS_COMPLETED)->count();
        $memberSince = $user->created_at ? $user->created_at->format('Y') : now()->format('Y');

        return view('customer.profile', [
            'profileData' => $profileData,
            'totalBookings' => $totalBookings,
            'completedServices' => $completedServices,
            'memberSince' => $memberSince,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $request->fullName,
            'phone' => $request->phone,
        ]);

        // Simple parser for address field to save back to database profile
        $addressInput = $request->address;
        $parts = explode(',', $addressInput);
        
        $address = trim($parts[0] ?? '');
        $city = trim($parts[1] ?? '');
        $province = trim($parts[2] ?? '');

        CustomerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address' => $address,
                'city' => $city,
                'province' => $province,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
        ]);
    }
}
