<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use App\Models\JobOrder;
use App\Models\ServiceStage;
use App\Models\ServiceStageProgress;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingApprovalService
{
    public function __construct(
        protected BookingStatusLogger $statusLogger,
    ) {}

    public function approve(Booking $booking, User $admin): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            throw new InvalidArgumentException('Only pending bookings can be approved.');
        }

        $reservationPayment = $booking->payments()
            ->reservationFees()
            ->latest()
            ->first();

        if ($reservationPayment === null || $reservationPayment->status !== Payment::STATUS_VERIFIED) {
            throw new InvalidArgumentException('Reservation fee must be verified before approval.');
        }

        return DB::transaction(function () use ($booking, $admin): Booking {
            $previousStatus = $booking->status;

            $booking->update([
                'status' => Booking::STATUS_APPROVED,
                'scheduled_date' => $booking->preferred_date,
                'scheduled_time' => $booking->preferred_time,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $quotation = $booking->quotations()
                ->latestVersion()
                ->first();

            if ($quotation !== null) {
                $quotation->update([
                    'status' => Quotation::STATUS_APPROVED,
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]);
            }

            // Create JobOrder automatically upon approval
            $this->createJobOrderForBooking($booking, $admin);

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                Booking::STATUS_APPROVED,
                $admin,
                'Booking approved by administrator.',
            );

            return $booking->fresh([
                'user',
                'vehicle',
                'bookingServices.service',
                'quotations',
                'payments',
                'statusLogs',
            ]);
        });
    }

    protected function createJobOrderForBooking(Booking $booking, User $admin): void
    {
        // Don't create duplicate JobOrders if somehow one already exists
        if ($booking->jobOrder()->exists()) {
            return;
        }

        $jobNumber = $this->generateJobNumber();

        // Create the JobOrder in a 'pending' (unassigned) state.
        // The admin will explicitly assign a mechanic via the Job Assignment page.
        $jobOrder = JobOrder::create([
            'job_number'     => $jobNumber,
            'booking_id'     => $booking->id,
            'mechanic_id'    => null,
            'assigned_by'    => null,
            'assigned_at'    => null,
            'status'         => JobOrder::STATUS_PENDING,
            'priority'       => JobOrder::PRIORITY_MEDIUM,
            'progress_percent' => 0,
        ]);

        // Seed initial stage progress rows so tracking works once assigned
        $stages = ServiceStage::orderBy('sort_order')->active()->get();
        foreach ($stages as $index => $stage) {
            ServiceStageProgress::create([
                'job_order_id'     => $jobOrder->id,
                'service_stage_id' => $stage->id,
                'is_completed'     => false,
                'is_current'       => ($index === 0),
            ]);
        }
    }

    protected function generateJobNumber(): string
    {
        $year = now()->year;
        $prefix = sprintf('JO-%d-', $year);

        $latest = JobOrder::withTrashed()
            ->where('job_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('job_number')
            ->value('job_number');

        $sequence = 1;

        if ($latest !== null) {
            $sequence = (int) substr($latest, strlen($prefix)) + 1;
        }

        return sprintf('%s%06d', $prefix, $sequence);
    }

    public function reject(Booking $booking, User $admin, string $reason): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING) {
            throw new InvalidArgumentException('Only pending bookings can be rejected.');
        }

        return DB::transaction(function () use ($booking, $admin, $reason): Booking {
            $previousStatus = $booking->status;

            $booking->update([
                'status' => Booking::STATUS_REJECTED,
                'rejection_reason' => $reason,
            ]);

            $quotation = $booking->quotations()->latestVersion()->first();

            if ($quotation !== null) {
                $quotation->update(['status' => Quotation::STATUS_REJECTED]);
            }

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                Booking::STATUS_REJECTED,
                $admin,
                $reason,
            );

            return $booking->fresh([
                'user',
                'vehicle',
                'bookingServices.service',
                'quotations',
                'payments',
                'statusLogs',
            ]);
        });
    }

    public function verifyReservationPayment(Booking $booking, User $admin): Payment
    {
        $payment = $booking->payments()
            ->reservationFees()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_SUBMITTED])
            ->latest()
            ->firstOrFail();

        $payment->update([
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        return $payment->fresh();
    }
}
