<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ResubmitPaymentRequest;
use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Payment;
use App\Services\Booking\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show($bookingId): View
    {
        $userId  = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->with([
                'services',
                'vehicle',
                'quotations'    => fn ($q) => $q->latestVersion()->limit(1),
                'payments.proofs',
            ])
            ->firstOrFail();

        $gcashNumber = BusinessSetting::getValue('gcash_account_number', '0912-345-6789');
        $mayaNumber  = BusinessSetting::getValue('maya_account_number', '0917-888-9999');
        $fee         = BusinessSetting::getValue('reservation_fee', 200.00);

        $quotation     = $booking->quotations->first();
        $totalEstimate = 'To be computed';
        if ($quotation) {
            $totalEstimate = $quotation->total_range_display;
        } else {
            $min = $booking->services->sum('min_cost');
            $max = $booking->services->sum('max_cost');
            if ($min > 0) {
                $totalEstimate = '₱'.number_format($min).' - ₱'.number_format($max);
            }
        }

        $bookingDetails = [
            'id'             => $booking->id,
            'booking_number' => $booking->booking_number,
            'service'        => $booking->services->first()?->name ?? 'Custom Customization',
            'vehicle'        => $booking->vehicle
                ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}"
                : 'Unknown',
            'reservationFee' => '₱'.number_format($fee, 2),
            'totalEstimate'  => $totalEstimate,
            'gcashNumber'    => $gcashNumber,
            'mayaNumber'     => $mayaNumber,
        ];

        // Always show the LATEST reservation payment (for status/rejection info)
        $payment       = $booking->payments->where('type', Payment::TYPE_RESERVATION_FEE)->sortByDesc('id')->first();
        $screenshotUrl = null;
        if ($payment) {
            $proof = $payment->proofs->first();
            if ($proof) {
                $screenshotUrl = $proof->url;
            }
        }

        // Pass the booking to the view so it can read status & payment_attempts
        return view('customer.payment', [
            'bookingId'      => $bookingId,
            'booking'        => $booking,
            'bookingDetails' => $bookingDetails,
            'payment'        => $payment,
            'screenshotUrl'  => $screenshotUrl,
            'maxAttempts'    => PaymentVerificationService::MAX_ATTEMPTS,
        ]);
    }

    public function submit(Request $request, $bookingId): RedirectResponse
    {
        $userId  = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->with('payments.proofs')
            ->firstOrFail();

        $payment = $booking->payments->where('type', Payment::TYPE_RESERVATION_FEE)->first();

        $request->validate([
            'payment_screenshot' => [$payment ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'reference_number'   => ['nullable', 'string'],
            'payment_method'     => ['nullable', 'string'],
        ]);

        if (! $payment) {
            $payment = Payment::create([
                'payment_number'   => 'PMT-'.strtoupper(uniqid()),
                'booking_id'       => $booking->id,
                'user_id'          => $userId,
                'type'             => Payment::TYPE_RESERVATION_FEE,
                'amount'           => BusinessSetting::getValue('reservation_fee', 200.00),
                'currency'         => 'PHP',
                'method'           => $request->input('payment_method'),
                'reference_number' => $request->input('reference_number'),
                'status'           => Payment::STATUS_SUBMITTED,
                'paid_at'          => now(),
            ]);
        } else {
            $payment->reference_number = $request->input('reference_number');
            $payment->method           = $request->input('payment_method');
            $payment->status           = Payment::STATUS_SUBMITTED;
            $payment->paid_at          = now();
            $payment->save();
        }

        if ($request->hasFile('payment_screenshot')) {
            foreach ($payment->proofs as $oldProof) {
                \Illuminate\Support\Facades\Storage::disk($oldProof->disk)->delete($oldProof->file_path);
                $oldProof->delete();
            }

            $file = $request->file('payment_screenshot');
            $path = $file->store('payment_proofs', 'public');
            $payment->proofs()->create([
                'disk'          => 'public',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'size_bytes'    => $file->getSize(),
            ]);
        }

        return redirect()->route('customer.payment', $bookingId)
            ->with('success', 'Payment details updated successfully! Your booking is now awaiting verification.');
    }

    /**
     * Customer resubmits payment after admin rejection.
     * Creates a new Payment record — preserving audit history.
     */
    public function resubmit(
        ResubmitPaymentRequest  $request,
        PaymentVerificationService $verification,
        int $bookingId,
    ): RedirectResponse {
        $userId  = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->firstOrFail();

        try {
            $verification->resubmit(
                booking: $booking,
                customer: $request->user(),
                data: $request->validated(),
                screenshot: $request->file('payment_screenshot'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('customer.payment', $bookingId)
            ->with('success', 'Payment resubmitted successfully! We will verify your payment shortly.');
    }
}
