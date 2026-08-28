<?php

namespace App\Services\Billing;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Booking\PaymentNumberGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingFinalPaymentService
{
    public function __construct(
        protected PaymentNumberGenerator $paymentNumberGenerator,
        protected BookingBillingCalculatorService $calculator,
    ) {}

    /**
     * @param  array{
     *     type: string,
     *     amount: float|int|string,
     *     method: string,
     *     reference_number?: string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function record(
        Booking $booking,
        User $recordedBy,
        array $data,
        ?UploadedFile $proof = null
    ): Payment {
        $allowedTypes = [Payment::TYPE_DEPOSIT, Payment::TYPE_FINAL_PAYMENT];
        if (! in_array($data['type'], $allowedTypes, true)) {
            throw new InvalidArgumentException("Invalid payment type '{$data['type']}'. Allowed: deposit, final_payment.");
        }

        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $currentSummary = $this->calculator->calculate($booking);
        if ($currentSummary->isFinalized && $amount > ($currentSummary->balanceDue + 0.01)) {
            throw new InvalidArgumentException(
                'Payment amount (₱'.number_format($amount, 2).') exceeds remaining balance due (₱'.number_format($currentSummary->balanceDue, 2).').'
            );
        }

        return DB::transaction(function () use ($booking, $recordedBy, $data, $proof, $amount): Payment {
            $isCash = in_array(strtolower($data['method']), [Payment::METHOD_CASH, 'cash'], true);
            $status = $isCash ? Payment::STATUS_VERIFIED : Payment::STATUS_SUBMITTED;

            $referenceNumber = ! empty($data['reference_number'])
                ? $data['reference_number']
                : ($isCash ? 'CASH-'.strtoupper(substr(uniqid(), -6)) : null);

            $payment = Payment::create([
                'payment_number' => $this->paymentNumberGenerator->generate(),
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'type' => $data['type'],
                'amount' => $amount,
                'currency' => 'PHP',
                'method' => strtolower($data['method']),
                'reference_number' => $referenceNumber,
                'status' => $status,
                'paid_at' => now(),
                'verified_by' => $isCash ? $recordedBy->id : null,
                'verified_at' => $isCash ? now() : null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($proof && $proof instanceof UploadedFile) {
                $path = $proof->store('payment_proofs', 'public');
                $payment->proofs()->create([
                    'disk' => 'public',
                    'file_path' => $path,
                    'original_name' => $proof->getClientOriginalName(),
                    'mime_type' => $proof->getClientMimeType(),
                    'size_bytes' => $proof->getSize(),
                ]);
            }

            // If quotation is finalized, refresh snapshot columns
            $finalQuotation = $booking->quotations()
                ->where('type', Quotation::TYPE_FINAL)
                ->where('status', Quotation::STATUS_APPROVED)
                ->latest('version')
                ->first();

            if ($finalQuotation) {
                $newSummary = $this->calculator->calculate($booking);
                $finalQuotation->update([
                    'amount_paid_snapshot' => $newSummary->totalPaid,
                    'balance_due_snapshot' => $newSummary->balanceDue,
                ]);
            }

            return $payment->load('proofs');
        });
    }
}
