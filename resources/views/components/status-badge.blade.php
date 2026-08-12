@props([
    'status' => null,
])

@php
    $styles = [
        'pending' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        'approved' => 'bg-green-500/10 text-green-500 border-green-500/20',
        'rejected' => 'bg-red-500/10 text-red-500 border-red-500/20',
        'waiting_payment' => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
        'waiting-payment' => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
        'pending_payment_verification' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        'pending-payment-verification' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
        'payment_requires_resubmission' => 'bg-red-500/10 text-red-500 border-red-500/20',
        'payment-requires-resubmission' => 'bg-red-500/10 text-red-500 border-red-500/20',
        'confirmed' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'scheduled' => 'bg-cyan-500/10 text-cyan-500 border-cyan-500/20',
        'in-progress' => 'bg-purple-500/10 text-purple-500 border-purple-500/20',
        'in_progress' => 'bg-purple-500/10 text-purple-500 border-purple-500/20',
        'completed' => 'bg-green-500/10 text-green-500 border-green-500/20',
        'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
        'open' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'resolved' => 'bg-green-500/10 text-green-500 border-green-500/20',
        'closed' => 'bg-gray-500/10 text-gray-500 border-gray-500/20',
        'active' => 'bg-green-500/10 text-green-500 border-green-500/20',
        'inactive' => 'bg-gray-500/10 text-gray-500 border-gray-500/20',
        'assigned' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'paused' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    ];

    $labels = [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'waiting_payment' => 'Waiting Payment',
        'waiting-payment' => 'Waiting Payment',
        'pending_payment_verification' => 'Pending Verification',
        'pending-payment-verification' => 'Pending Verification',
        'payment_requires_resubmission' => 'Payment Rejected',
        'payment-requires-resubmission' => 'Payment Rejected',
        'confirmed' => 'Confirmed',
        'scheduled' => 'Scheduled',
        'in-progress' => 'In Progress',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'open' => 'Open',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'assigned' => 'Assigned',
        'paused' => 'Paused',
    ];

    $alpineStatus = $attributes->get(':status') ?? $attributes->get('status');
@endphp

@if($status)
    @php
        $classes = 'inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold border ' . ($styles[$status] ?? 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20');
        $displayText = $slot->isNotEmpty() ? $slot : ($labels[$status] ?? ucwords(str_replace(['_', '-'], ' ', $status)));
    @endphp
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $displayText }}
    </span>
@else
    @php
        $baseClass = 'inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold border';
        $stylesJson = json_encode($styles);
        $labelsJson = json_encode($labels);
    @endphp
    <span 
        {{ $attributes->except(['status', ':status']) }}
        class="{{ $baseClass }}"
        :class="({{ $stylesJson }})[{{ $alpineStatus }}] || 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20'"
    >
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            <span x-text="({{ $labelsJson }})[{{ $alpineStatus }}] || ({{ $alpineStatus }} ? {{ $alpineStatus }}.replace(/_/g, ' ').replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '')"></span>
        @endif
    </span>
@endif

