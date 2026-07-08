@extends('layouts.dashboard')

@section('title', 'Customers | Staff | AutoProject+')

@section('content')
<div class="space-y-8 animate-fade-in">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Customers</h1>
            <p class="text-gray-600 dark:text-gray-400">Browse and view customer profiles.</p>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('staff.customers.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Search by name, email or phone..."
                class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50 w-64">
            <x-button type="submit" variant="secondary" size="sm">Search</x-button>
            @if ($search)
                <a href="{{ route('staff.customers.index') }}">
                    <x-button type="button" variant="ghost" size="sm">Clear</x-button>
                </a>
            @endif
        </form>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vehicles</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bookings</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#D2781A]/20 flex items-center justify-center flex-shrink-0">
                                        <span class="text-[#D2781A] font-bold text-sm">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                    </div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $customer->name }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $customer->email }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $customer->vehicles->count() }} vehicle{{ $customer->vehicles->count() !== 1 ? 's' : '' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $customer->bookings_count }}
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ route('staff.customers.show', $customer) }}">
                                    <x-button size="sm" variant="ghost">View</x-button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                @if ($search)
                                    No customers found matching "{{ $search }}".
                                @else
                                    No customers registered yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($customers->hasPages())
            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
