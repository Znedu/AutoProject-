<?php

namespace App\Http\Controllers\Staff;

use App\Enums\RoleSlug;
use App\Exceptions\Booking\ScheduleNotAvailableException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\Booking\WalkInBookingCreatedNotification;
use App\Services\Booking\BookingCreatorService;
use App\Services\Booking\BookingNumberGenerator;
use App\Services\Booking\QuotationBuilderService;
use App\Services\Booking\ScheduleAvailabilityService;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalkInBookingController extends Controller
{
    public function create(): View
    {
        $serviceCategories = ServiceCategory::query()
            ->active()
            ->ordered()
            ->with(['services' => fn ($query) => $query->active()->with('brands')])
            ->get();

        $services = Service::query()
            ->active()
            ->with(['category', 'brands'])
            ->get();

        $customers = User::customers()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?? '',
            ]);

        $fee          = \App\Models\BusinessSetting::getValue('reservation_fee', 200.00);
        $gcashNumber  = \App\Models\BusinessSetting::getValue('gcash_account_number', '0912-345-6789');
        $mayaNumber   = \App\Models\BusinessSetting::getValue('maya_account_number', '0917-888-9999');

        return view('staff.walk-in-booking', [
            'serviceCategories' => $serviceCategories,
            'services'          => $services,
            'customers'         => $customers,
            'fee'               => $fee,
            'gcashNumber'       => $gcashNumber,
            'mayaNumber'        => $mayaNumber,
        ]);
    }

    public function store(
        Request $request,
        BookingCreatorService $creator
    ): RedirectResponse {
        $request->validate([
            'booking_type'     => ['required', 'in:existing,new'],
            'customer_id'      => ['required_if:booking_type,existing', 'nullable', 'integer', 'exists:users,id'],
            'customer_name'    => ['required', 'string', 'max:255'],
            'contact_number'   => ['required', 'string', 'max:50'],
            // New customer fields
            'new_email'        => ['required_if:booking_type,new', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'new_password'     => ['required_if:booking_type,new', 'nullable', 'string', 'min:8'],
            // Vehicle
            'vehicle_make'     => ['required', 'string', 'max:100'],
            'vehicle_model'    => ['required', 'string', 'max:100'],
            'vehicle_year'     => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 2)],
            'plate_number'     => ['required', 'string', 'max:30'],
            // Booking
            'service_ids'      => ['required', 'array', 'min:1'],
            'service_ids.*'    => ['integer', 'exists:services,id'],
            'preferred_date'   => ['required', 'date', 'after_or_equal:today'],
            'preferred_time'   => ['required', 'string'],
            'notes'            => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Resolve or create the customer
            if ($request->booking_type === 'new') {
                $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
                $customer = User::create([
                    'name'     => $request->customer_name,
                    'email'    => $request->new_email,
                    'phone'    => $request->contact_number,
                    'role_id'  => $customerRole->id,
                    'status'   => User::STATUS_ACTIVE,
                    'password' => $request->new_password,
                ]);
            } else {
                $customer = User::findOrFail($request->customer_id);
            }

            $booking = $creator->create($customer, [
                'service_ids'    => $request->service_ids,
                'brands'         => $request->input('brands', []),
                'customer_name'  => $request->customer_name,
                'contact_number' => $request->contact_number,
                'vehicle_make'   => $request->vehicle_make,
                'vehicle_model'  => $request->vehicle_model,
                'vehicle_year'   => $request->vehicle_year,
                'plate_number'   => $request->plate_number,
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'notes'          => $request->notes,
                // Walk-ins don't have an online payment — reservation fee is collected in-store
                'payment_method'   => 'cash',
                'reference_number' => 'WALKIN-' . strtoupper(Str::random(8)),
            ]);

            // Walk-in customer: automatically confirm payment, lock schedule slot & create job order
            $booking->update([
                'is_walk_in'     => true,
                'status'         => Booking::STATUS_CONFIRMED,
                'scheduled_date' => $booking->preferred_date,
                'scheduled_time' => $booking->preferred_time,
                'approved_at'    => now(),
            ]);

            // Mark in-store cash payment as verified
            $payment = $booking->payments()->reservationFees()->latest()->first();
            if ($payment) {
                $payment->update([
                    'status'      => Payment::STATUS_VERIFIED,
                    'verified_at' => now(),
                ]);
            }

            // Create JobOrder automatically for walk-in
            app(\App\Services\Booking\BookingApprovalService::class)->createJobOrderForBooking($booking, auth()->user() ?? $customer);

            DB::commit();

            $dispatcher = app(NotificationDispatcherService::class);
            $dispatcher->notifyAdmins(new WalkInBookingCreatedNotification($booking));
            $dispatcher->notifyUser($customer, new WalkInBookingCreatedNotification($booking));

            return redirect()
                ->route('staff.booking-queue')
                ->with('success', "Walk-in booking #{$booking->booking_number} created successfully for {$customer->name}.");
        } catch (ScheduleNotAvailableException $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['preferred_time' => $e->getMessage()]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * JSON search — returns matching customers (used by Alpine.js on the walk-in form).
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        $customers = User::customers()
            ->active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?? '',
            ]);

        return response()->json($customers);
    }

    /**
     * JSON lookup — returns vehicles belonging to a specific customer.
     */
    public function getCustomerVehicles(User $user): JsonResponse
    {
        $vehicles = $user->vehicles()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($v) => [
                'id'           => $v->id,
                'make'         => $v->make,
                'model'        => $v->model,
                'year'         => $v->year,
                'plate_number' => $v->plate_number,
            ]);

        return response()->json($vehicles);
    }
}
