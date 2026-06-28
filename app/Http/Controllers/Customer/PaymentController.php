<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function show($bookingId)
    {
        $userId = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->with([
                'services', 
                'vehicle', 
                'quotations' => fn($q) => $q->latestVersion()->limit(1),
                'payments.proofs'
            ])
            ->firstOrFail();

        // Get reservation fee from business settings
        $fee = BusinessSetting::getValue('reservation_fee', 200.00);
        $gcashNumber = BusinessSetting::getValue('gcash_account_number', '0912-345-6789');
        $mayaNumber = BusinessSetting::getValue('maya_account_number', '0917-888-9999');

        // Estimate total
        $quotation = $booking->quotations->first();
        $totalEstimate = 'To be computed';
        if ($quotation) {
            $totalEstimate = $quotation->total_range_display;
        } else {
            // Compute range from booking services
            $min = $booking->services->sum('min_cost');
            $max = $booking->services->sum('max_cost');
            if ($min > 0) {
                $totalEstimate = '₱' . number_format($min) . ' - ₱' . number_format($max);
            }
        }

        $bookingDetails = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'service' => $booking->services->first()?->name ?? 'Custom Customization',
            'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown',
            'reservationFee' => '₱' . number_format($fee, 2),
            'totalEstimate' => $totalEstimate,
            'gcashNumber' => $gcashNumber,
            'mayaNumber' => $mayaNumber,
        ];

        $payment = $booking->payments->where('type', \App\Models\Payment::TYPE_RESERVATION_FEE)->first();
        $screenshotUrl = null;
        if ($payment) {
            $proof = $payment->proofs->first();
            if ($proof) {
                $screenshotUrl = $proof->url;
            }
        }

        return view('customer.payment', [
            'bookingId' => $bookingId,
            'bookingDetails' => $bookingDetails,
            'payment' => $payment,
            'screenshotUrl' => $screenshotUrl,
        ]);
    }

    public function submit(Request $request, $bookingId)
    {
        $userId = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->with('payments.proofs')
            ->firstOrFail();

        $payment = $booking->payments->where('type', Payment::TYPE_RESERVATION_FEE)->first();

        $request->validate([
            'payment_screenshot' => [$payment ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'reference_number' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string'],
        ]);

        if (! $payment) {
            $payment = Payment::create([
                'payment_number' => 'PMT-' . strtoupper(uniqid()),
                'booking_id' => $booking->id,
                'user_id' => $userId,
                'type' => Payment::TYPE_RESERVATION_FEE,
                'amount' => BusinessSetting::getValue('reservation_fee', 200.00),
                'currency' => 'PHP',
                'method' => $request->input('payment_method'),
                'reference_number' => $request->input('reference_number'),
                'status' => Payment::STATUS_SUBMITTED,
                'paid_at' => now(),
            ]);
        } else {
            $payment->reference_number = $request->input('reference_number');
            $payment->method = $request->input('payment_method');
            $payment->status = Payment::STATUS_SUBMITTED;
            $payment->paid_at = now();
            $payment->save();
        }

        if ($request->hasFile('payment_screenshot')) {
            // Delete old proofs
            foreach ($payment->proofs as $oldProof) {
                if (Storage::disk($oldProof->disk)->exists($oldProof->file_path)) {
                    Storage::disk($oldProof->disk)->delete($oldProof->file_path);
                }
                $oldProof->delete();
            }

            $file = $request->file('payment_screenshot');
            $path = $file->store('payment_proofs', 'public');
            $payment->proofs()->create([
                'disk' => 'public',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        return redirect()->route('customer.payment', $bookingId)
            ->with('success', 'Payment details updated successfully! Your booking is now awaiting verification.');
    }
}
