<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\Booking\ScheduleNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\Booking\BookingCancellationService;
use App\Services\Booking\BookingCreatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $user = $request->user();

        $serviceCategories = ServiceCategory::query()
            ->active()
            ->ordered()
            ->with(['services' => fn ($query) => $query->active()->with('brands')])
            ->get();

        $services = Service::query()
            ->active()
            ->with(['category', 'brands'])
            ->get();

        return view('customer.book-service', [
            'user' => $user,
            'serviceCategories' => $serviceCategories,
            'services' => $services,
            'vehicles' => $user->vehicles()->latest()->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $bookings = Booking::query()
            ->forUser($request->user()->id)
            ->with([
                'vehicle',
                'bookingServices.service',
                'quotations' => fn ($query) => $query->latestVersion()->limit(1),
                'payments' => fn ($query) => $query->reservationFees()->latest()->limit(1),
            ])
            ->when($status && $status !== 'all', fn ($query) => $query->status($status))
            ->latest()
            ->get();

        return view('customer.bookings', [
            'bookings' => $bookings,
            'selectedFilter' => $status ?? 'all',
        ]);
    }

    public function store(StoreBookingRequest $request, BookingCreatorService $creator): RedirectResponse
    {
        try {
            $booking = $creator->create($request->user(), $request->bookingPayload());
        } catch (ScheduleNotAvailableException $exception) {
            return back()
                ->withInput()
                ->withErrors(['preferred_time' => $exception->getMessage()]);
        }

        return redirect()
            ->route('customer.bookings.index')
            ->with('success', 'Booking '.$booking->booking_number.' submitted successfully! Our team will verify your payment and confirm your appointment.');
    }

    public function destroy(CancelBookingRequest $request, Booking $booking, BookingCancellationService $cancellation): RedirectResponse
    {
        try {
            $cancellation->cancel($booking, $request->user(), $request->input('reason'));
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('customer.bookings.index', ['status' => 'cancelled'])
            ->with('success', 'Booking cancelled successfully.');
    }
}
