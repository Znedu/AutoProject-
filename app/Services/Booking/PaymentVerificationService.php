<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\Booking\BookingConfirmedNotification;
use App\Notifications\Job\JobOrderCreatedNotification;
use App\Notifications\Payment\BookingAutoCancelledNotification;
use App\Notifications\Payment\PaymentRejectedNotification;
use App\Notifications\Payment\PaymentResubmittedNotification;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentVerificationService
{
    /**
     * Maximum number of payment rejections allowed before a booking is
     * automatically cancelled.
     *
     * NOTE: This will be made configurable via BusinessSetting in a future
     * iteration (admin panel integration is deferred). See implementation_plan.md.
     */
    public const MAX_ATTEMPTS = 3;

    public function __construct(
        protected BookingStatusLogger   $statusLogger,
        protected BookingApprovalService $approvalService,
        protected PaymentNumberGenerator $paymentNumberGenerator,
        protected NotificationDispatcherService $dispatcher,
    ) {}

    // -------------------------------------------------------------------------
    // Admin Actions
    // -------------------------------------------------------------------------

    /**
     * Admin verifies the payment and confirms the booking in a single action.
     *
     * Transition: pending_payment_verification → confirmed
     */
    public function confirm(Booking $booking, User $admin): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT_VERIFICATION) {
            throw new InvalidArgumentException(
                'Only bookings with status "pending_payment_verification" can be confirmed.'
            );
        }

        $payment = $booking->payments()
            ->reservationFees()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_SUBMITTED])
            ->latest()
            ->firstOrFail();

        $booking = DB::transaction(function () use ($booking, $admin, $payment): Booking {
            $previousStatus = $booking->status;

            // Verify the payment
            $payment->update([
                'status'      => Payment::STATUS_VERIFIED,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            // Confirm and lock in the scheduled appointment
            $booking->update([
                'status'         => Booking::STATUS_CONFIRMED,
                'approved_by'    => $admin->id,
                'approved_at'    => now(),
                'scheduled_date' => $booking->preferred_date,
                'scheduled_time' => $booking->preferred_time,
            ]);

            // Approve the latest quotation
            $quotation = $booking->quotations()->latestVersion()->first();
            if ($quotation !== null) {
                $quotation->update([
                    'status'      => Quotation::STATUS_APPROVED,
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]);
            }

            // Auto-create job order (same logic as BookingApprovalService::approve)
            $this->approvalService->createJobOrderForBooking($booking, $admin);

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                Booking::STATUS_CONFIRMED,
                $admin,
                'Payment verified and booking confirmed by administrator.',
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

        if ($booking->user) {
            $this->dispatcher->notifyUser($booking->user, new BookingConfirmedNotification($booking));
        }

        $jobOrder = $booking->jobOrder ?? $booking->fresh(['jobOrder'])->jobOrder;
        if ($jobOrder) {
            $this->dispatcher->notifyAdmins(new JobOrderCreatedNotification($jobOrder));
        }

        return $booking;
    }

    /**
     * Admin rejects the customer's payment proof.
     * Increments payment_attempts. Cancels the booking if MAX_ATTEMPTS reached.
     *
     * Transition: pending_payment_verification → payment_requires_resubmission
     *                                          → cancelled (if attempts exhausted)
     */
    public function rejectPayment(Booking $booking, User $admin, string $reason): Booking
    {
        if ($booking->status !== Booking::STATUS_PENDING_PAYMENT_VERIFICATION) {
            throw new InvalidArgumentException(
                'Only bookings with status "pending_payment_verification" can have their payment rejected.'
            );
        }

        $payment = $booking->payments()
            ->reservationFees()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_SUBMITTED])
            ->latest()
            ->firstOrFail();

        $booking = DB::transaction(function () use ($booking, $admin, $payment, $reason): Booking {
            $previousStatus = $booking->status;

            // Mark this payment attempt as rejected
            $payment->update([
                'status'           => Payment::STATUS_REJECTED,
                'verified_by'      => $admin->id,
                'verified_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            // Increment attempt counter
            $newAttempts = $booking->payment_attempts + 1;

            $newBookingStatus = $newAttempts >= self::MAX_ATTEMPTS
                ? Booking::STATUS_CANCELLED
                : Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION;

            $booking->update([
                'payment_attempts' => $newAttempts,
                'status'           => $newBookingStatus,
            ]);

            $logNote = $newBookingStatus === Booking::STATUS_CANCELLED
                ? "Booking cancelled after {$newAttempts} failed payment verification attempts. Last reason: {$reason}"
                : "Payment rejected by administrator (attempt {$newAttempts} of ".self::MAX_ATTEMPTS.'). Reason: '.$reason;

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                $newBookingStatus,
                $admin,
                $logNote,
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

        if ($booking->status === Booking::STATUS_CANCELLED) {
            if ($booking->user) {
                $this->dispatcher->notifyUser($booking->user, new BookingAutoCancelledNotification($booking));
            }
            $this->dispatcher->notifyAdmins(new BookingAutoCancelledNotification($booking));
        } else {
            if ($booking->user) {
                $this->dispatcher->notifyUser($booking->user, new PaymentRejectedNotification($booking, $reason));
            }
        }

        return $booking;
    }

    // -------------------------------------------------------------------------
    // Customer Actions
    // -------------------------------------------------------------------------

    /**
     * Customer resubmits payment proof after a rejection.
     * Creates a NEW Payment record so all attempts are preserved in history.
     *
     * Transition: payment_requires_resubmission → pending_payment_verification
     */
    public function resubmit(Booking $booking, User $customer, array $data, UploadedFile $screenshot): Payment
    {
        if ($booking->status !== Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION) {
            throw new InvalidArgumentException(
                'Payment can only be resubmitted when booking status is "payment_requires_resubmission".'
            );
        }

        if ($booking->payment_attempts >= self::MAX_ATTEMPTS) {
            throw new InvalidArgumentException(
                'Maximum payment resubmission attempts reached. This booking has been cancelled.'
            );
        }

        $payment = DB::transaction(function () use ($booking, $customer, $data, $screenshot): Payment {
            $previousStatus = $booking->status;

            // Create a fresh payment record — keeps full audit trail
            $payment = Payment::create([
                'payment_number'   => $this->paymentNumberGenerator->generate(),
                'booking_id'       => $booking->id,
                'user_id'          => $customer->id,
                'type'             => Payment::TYPE_RESERVATION_FEE,
                'amount'           => $booking->payments()->reservationFees()->latest()->value('amount') ?? 200.00,
                'currency'         => 'PHP',
                'method'           => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'status'           => Payment::STATUS_SUBMITTED,
                'paid_at'          => now(),
            ]);

            // Store the new proof
            $path = $screenshot->store('payment_proofs', 'public');
            $payment->proofs()->create([
                'disk'          => 'public',
                'file_path'     => $path,
                'original_name' => $screenshot->getClientOriginalName(),
                'mime_type'     => $screenshot->getClientMimeType(),
                'size_bytes'    => $screenshot->getSize(),
            ]);

            // Back to pending verification
            $booking->update(['status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION]);

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
                $customer,
                'Customer resubmitted payment proof (attempt '.($booking->payment_attempts + 1).' of '.self::MAX_ATTEMPTS.').',
            );

            return $payment->fresh('proofs');
        });

        $this->dispatcher->notifyAdminsWithPermission('approvals.manage', new PaymentResubmittedNotification($booking));

        return $payment;
    }
}
