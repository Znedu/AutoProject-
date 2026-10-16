<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Record #{{ $booking->booking_number }} - AutoProject+</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.5;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #E63946;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-title {
            font-size: 26px;
            font-weight: 800;
            color: #E63946;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        .meta-box {
            text-align: right;
            font-size: 12px;
        }
        .meta-box strong {
            font-size: 14px;
            color: #000;
        }
        .badge {
            display: inline-block;
            background: #E63946;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .grid {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        .col {
            flex: 1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .col h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .col p {
            margin: 4px 0;
            font-size: 13px;
        }
        .col strong {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background: #111827;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 8px 12px;
            text-align: left;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .financial-summary {
            margin-left: auto;
            width: 300px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 30px;
        }
        .financial-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin: 5px 0;
        }
        .financial-row.total {
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
        }
        .sign-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #9ca3af;
            padding-top: 5px;
            font-size: 12px;
            color: #4b5563;
        }
        .print-btn {
            background: #E63946;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button class="print-btn" onclick="window.print()">Print Service Record / Save PDF</button>
    </div>

    <div class="header">
        <div>
            <h1 class="logo-title">AutoProject+</h1>
            <p class="subtitle">Automotive Customization, Paint & Performance Workshop</p>
            <p class="subtitle">Official Maintenance &amp; Service History Record</p>
        </div>
        <div class="meta-box">
            <div class="badge">Official Certificate</div>
            <p>Record #: <strong>{{ $booking->booking_number }}</strong></p>
            <p>Service Date: <strong>{{ $booking->completed_at ? $booking->completed_at->format('F d, Y') : ($booking->scheduled_date ? $booking->scheduled_date->format('F d, Y') : now()->format('F d, Y')) }}</strong></p>
            <p>Job Order: <strong>{{ $booking->jobOrder?->job_number ?? 'JO-COMPLETED' }}</strong></p>
        </div>
    </div>

    <div class="grid">
        <div class="col">
            <h3>Vehicle Identification</h3>
            <p>Make & Model: <strong>{{ $booking->vehicle?->make }} {{ $booking->vehicle?->model }}</strong></p>
            <p>Model Year: <strong>{{ $booking->vehicle?->year }}</strong></p>
            <p>Plate Number: <strong>{{ $booking->vehicle?->plate_number }}</strong></p>
            <p>Body Color: <strong>{{ $booking->vehicle?->color ?? 'Standard OEM' }}</strong></p>
        </div>

        <div class="col">
            <h3>Customer & Workshop</h3>
            <p>Customer Name: <strong>{{ $booking->customer_name ?? $booking->user?->name }}</strong></p>
            <p>Contact Phone: <strong>{{ $booking->contact_number ?? $booking->user?->phone }}</strong></p>
            <p>Assigned Mechanic: <strong>{{ $booking->jobOrder?->mechanic?->name ?? 'Workshop Technical Lead' }}</strong></p>
            <p>Status: <strong style="color: #059669;">{{ strtoupper($booking->status) }}</strong></p>
        </div>
    </div>

    <h3>Customization Services & Work Details</h3>
    <table>
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Category</th>
                <th>Brand / Parts Specification</th>
                <th style="text-align: right;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($booking->bookingServices as $bs)
                <tr>
                    <td><strong>{{ $bs->service?->name ?? 'Custom Service' }}</strong></td>
                    <td>{{ $bs->service?->category?->name ?? 'General Customization' }}</td>
                    <td>{{ $bs->preferred_brand ?? 'Shop Certified OEM' }}</td>
                    <td style="text-align: right; color: #059669; font-weight: bold;">Completed</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Automotive customization and service performed.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($booking->paintScope || $booking->paintPanels->isNotEmpty())
        <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 12px 15px; margin-bottom: 25px;">
            <h3 style="color: #6d28d9; margin-top: 0; font-size: 12px; text-transform: uppercase;">Paint & Bodywork Specifications</h3>
            @if ($booking->paintScope)
                <p style="font-size: 12px; margin: 4px 0;">
                    Scope Mode: <strong>{{ ucfirst($booking->paintScope->scope_mode) }}</strong> |
                    Color Code: <strong>{{ $booking->paintScope->color_code ?: 'OEM Color Code Matched' }}</strong> |
                    Finish Type: <strong>{{ ucfirst($booking->paintScope->finish_type ?: 'Gloss') }}</strong>
                </p>
            @endif
            @if ($booking->paintPanels->isNotEmpty())
                <p style="font-size: 12px; margin: 4px 0;">
                    Repaired / Custom Painted Panels:
                    @foreach ($booking->paintPanels as $panel)
                        <strong>{{ $panel->panel?->name ?? ucwords(str_replace('_', ' ', $panel->panel_code)) }} ({{ $panel->severity_label ?? ucfirst(str_replace('_', ' ', $panel->severity ?? '')) }})</strong>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            @endif
        </div>
    @endif

    <div class="financial-summary">
        <h3 style="margin-top: 0; font-size: 12px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Financial Settlement</h3>
        <div class="financial-row">
            <span>Total Charges:</span>
            <span>₱{{ number_format($summary->finalTotal, 2) }}</span>
        </div>
        @if ($summary->reservationFeePaid > 0)
            <div class="financial-row" style="color: #2563eb;">
                <span>Prepaid Reservation Fee Credited:</span>
                <span>-₱{{ number_format($summary->reservationFeePaid, 2) }}</span>
            </div>
        @endif
        <div class="financial-row total">
            <span>Total Settled:</span>
            <span style="color: #059669;">₱{{ number_format($summary->totalPaid, 2) }}</span>
        </div>
        @if ($summary->balanceDue > 0)
            <div class="financial-row" style="color: #dc2626; font-weight: bold;">
                <span>Remaining Balance Due:</span>
                <span>₱{{ number_format($summary->balanceDue, 2) }}</span>
            </div>
        @endif
    </div>

    <div class="signatures">
        <div class="sign-box">
            Lead Mechanic Signature
        </div>
        <div class="sign-box">
            Customer Acknowledgment
        </div>
    </div>
</body>
</html>
