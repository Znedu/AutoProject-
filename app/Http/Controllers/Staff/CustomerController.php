<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search', '');

        $customers = User::customers()
            ->with('vehicles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('bookings')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('staff.customers.index', [
            'customers' => $customers,
            'search'    => $search,
        ]);
    }

    public function show(User $user): View
    {
        // Ensure only customer profiles are viewable by Staff
        abort_unless($user->isCustomer(), 404);

        $user->load([
            'vehicles',
            'bookings' => fn ($q) => $q->with(['services', 'vehicle'])
                ->latest()
                ->limit(20),
        ]);

        $bookings = $user->bookings->map(fn ($b) => [
            'id'             => $b->id,
            'booking_number' => $b->booking_number,
            'service'        => $b->services->first()?->name ?? 'Custom Service',
            'vehicle'        => $b->vehicle
                ? "{$b->vehicle->make} {$b->vehicle->model} {$b->vehicle->year}"
                : 'Unknown',
            'preferred_date' => $b->preferred_date?->format('F d, Y') ?? 'N/A',
            'status'         => $b->status,
            'is_walk_in'     => $b->is_walk_in,
        ]);

        return view('staff.customers.show', [
            'customer' => $user,
            'bookings' => $bookings,
        ]);
    }
}
