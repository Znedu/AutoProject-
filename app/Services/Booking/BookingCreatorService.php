<?php

namespace App\Services\Booking;

use App\Exceptions\Booking\ScheduleNotAvailableException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingCreatorService
{
    public const RESERVATION_FEE = 200.00;

    public function __construct(
        protected BookingNumberGenerator $bookingNumberGenerator,
        protected PaymentNumberGenerator $paymentNumberGenerator,
        protected ScheduleAvailabilityService $scheduleAvailability,
        protected QuotationBuilderService $quotationBuilder,
        protected BookingStatusLogger $statusLogger,
    ) {}

    /**
     * @param  array{
     *     service_ids: list<int>,
     *     brands?: array<int, string|null>,
     *     customer_name: string,
     *     contact_number: string,
     *     vehicle_make: string,
     *     vehicle_model: string,
     *     vehicle_year: int,
     *     plate_number: string,
     *     preferred_date: string,
     *     preferred_time: string,
     *     notes?: string|null,
     *     payment_method: string,
     *     reference_number: string,
     *     payment_screenshot?: \Illuminate\Http\UploadedFile|null,
     * }  $data
     */
    public function create(User $customer, array $data): Booking
    {
        if (! $this->scheduleAvailability->isSlotAvailable($data['preferred_date'], $data['preferred_time'])) {
            throw new ScheduleNotAvailableException();
        }

        $services = Service::query()
            ->active()
            ->whereIn('id', $data['service_ids'])
            ->get();

        if ($services->count() !== count($data['service_ids'])) {
            throw new \InvalidArgumentException('One or more selected services are invalid or inactive.');
        }

        return DB::transaction(function () use ($customer, $data, $services): Booking {
            $vehicle = $this->resolveVehicle($customer, $data);
            $normalizedTime = $this->scheduleAvailability->normalizeTime($data['preferred_time']);

            $booking = Booking::create([
                'booking_number' => $this->bookingNumberGenerator->generate(),
                'user_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'status' => Booking::STATUS_PENDING,
                'preferred_date' => $data['preferred_date'],
                'preferred_time' => $normalizedTime,
                'customer_name' => $data['customer_name'],
                'contact_number' => $data['contact_number'],
                'notes' => $data['notes'] ?? null,
                'terms_accepted_at' => now(),
            ]);

            $brandPreferences = $data['brands'] ?? [];

            foreach ($services as $service) {
                $booking->bookingServices()->create([
                    'service_id' => $service->id,
                    'preferred_brand' => $brandPreferences[$service->id] ?? null,
                ]);
            }

            $this->quotationBuilder->createInitialEstimate($booking, $services, $brandPreferences);

            $payment = Payment::create([
                'payment_number' => $this->paymentNumberGenerator->generate(),
                'booking_id' => $booking->id,
                'user_id' => $customer->id,
                'type' => Payment::TYPE_RESERVATION_FEE,
                'amount' => self::RESERVATION_FEE,
                'currency' => 'PHP',
                'method' => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'status' => Payment::STATUS_SUBMITTED,
                'paid_at' => now(),
            ]);

            if (isset($data['payment_screenshot']) && $data['payment_screenshot'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['payment_screenshot'];
                $path = $file->store('payment_proofs', 'public');
                $payment->proofs()->create([
                    'disk' => 'public',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }

            $this->statusLogger->log($booking, null, Booking::STATUS_PENDING, $customer, 'Booking submitted by customer.');

            return $booking->load([
                'vehicle',
                'bookingServices.service',
                'quotations.lineItems',
                'payments',
            ]);
        });
    }

    /**
     * @param  array{
     *     vehicle_make: string,
     *     vehicle_model: string,
     *     vehicle_year: int,
     *     plate_number: string,
     * }  $data
     */
    protected function resolveVehicle(User $customer, array $data): Vehicle
    {
        return Vehicle::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'plate_number' => strtoupper(trim($data['plate_number'])),
            ],
            [
                'make' => $data['vehicle_make'],
                'model' => $data['vehicle_model'],
                'year' => $data['vehicle_year'],
            ],
        );
    }
}
